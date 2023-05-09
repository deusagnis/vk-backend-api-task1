<?php

namespace App\Microservice\Facades;

use App\Microservice\DistributeEvent;
use App\Microservice\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MGGFLOW\ExceptionManager\Interfaces\UniException;
use MGGFLOW\ExceptionManager\ManageException;
use MGGFLOW\ExceptionManager\UnexpectedError;
use Throwable;

/**
 * Добавление нового События.
 */
class EventAdding
{
    private Request $request;

    private array $eventFields;
    private bool $eventCreated;

    /**
     * Инициализировать добавление События.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Добавить Событие.
     * @return bool Флаг успешности добавления.
     * @throws UniException
     */
    public function add(): bool
    {
        $this->validateRequest();
        $this->parseRequest();
        $this->fillRemainingFields();
        $this->createEvent();

        return $this->getResult();
    }

    private function getResult(): bool
    {
        return $this->eventCreated;
    }

    /**
     * @throws UniException
     */
    private function createEvent()
    {
        try {
            DB::connection()->transaction(function () {
                $this->eventCreated = Event::query()->insert($this->eventFields);

                $distribution = new DistributeEvent();
                $distribution->distribute($this->eventFields);
            }, 3);
        } catch (Throwable $e) {
            throw ManageException::build((new UnexpectedError())->import($e))
                ->log()->error()->b()
                ->desc()->internal()->b()
                ->fill();
        }
    }

    private function fillRemainingFields()
    {
        $this->eventFields['ip'] = $this->request->ip();
        $this->eventFields['created_at'] = now()->unix();
    }

    private function parseRequest()
    {
        $this->eventFields = [];

        $this->eventFields['name'] = $this->request->input('name');
        $this->eventFields['user_status'] = $this->request->input(
            'userStatus',
            Event::USER_STATUS_UNAUTHORISED
        );
    }

    /**
     * @throws UniException
     */
    private function validateRequest()
    {
        $validator = Validator::make(
            $this->request->all(),
            [
                'name' => ['required', 'string', 'max:' . Event::MAX_EVENT_NAME_LENGTH],
                'userStatus' => ['integer', Rule::in([
                    Event::USER_STATUS_UNAUTHORISED,
                    Event::USER_STATUS_AUTHORISED
                ])],
            ]
        );

        if ($validator->fails()) {
            throw ManageException::build()
                ->log()->info()->b()
                ->desc()->areInvalid('Event Data')
                ->context($validator->errors()->toArray(), 'errors')->b()
                ->fill();
        }
    }
}
