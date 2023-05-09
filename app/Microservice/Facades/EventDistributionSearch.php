<?php

namespace App\Microservice\Facades;

use App\Microservice\Models\Event;
use App\Microservice\Models\EventsDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MGGFLOW\ExceptionManager\Interfaces\UniException;
use MGGFLOW\ExceptionManager\ManageException;

/**
 * Поиск количества Событий из их распределения по датам.
 */
class EventDistributionSearch
{
    private Request $request;
    private array $eventSearchFields;
    private string $searchHash;

    private int $counter;

    /**
     * Инициализировать поиск количества Событий.
     * @param Request $request Экземпляр запроса.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Определить количество Событий по параметрам, указанным в запросе.
     * @return int Счетчик событий.
     * @throws UniException
     */
    public function find(): int
    {
        $this->validateRequest();
        $this->parseRequest();
        $this->prepareSearchHash();
        $this->searchDistribution();

        return $this->getResult();
    }

    private function getResult(): int
    {
        return $this->counter;
    }

    private function searchDistribution()
    {
        $response = EventsDistribution::query()
            ->where('events_hash', $this->searchHash)
            ->first('counter');

        $this->counter = $response->counter ?? 0;
    }

    private function prepareSearchHash()
    {
        $hasIp = isset($this->eventSearchFields['ip']);
        $hasUserStatus = isset($this->eventSearchFields['user_status']);

        if ($hasIp and $hasUserStatus) {
            $this->searchHash = EventsDistribution::genExactHash($this->eventSearchFields);
            return;
        }

        if ($hasIp) {
            $this->searchHash = EventsDistribution::genIpHash($this->eventSearchFields);
            return;
        }

        if ($hasUserStatus) {
            $this->searchHash = EventsDistribution::genUserStatusHash($this->eventSearchFields);
            return;
        }

        $this->searchHash = EventsDistribution::genCommonHash($this->eventSearchFields);
    }

    private function parseRequest()
    {
        $this->eventSearchFields = [];

        $this->eventSearchFields['name'] = $this->request->input('name');
        $this->eventSearchFields['created_at'] = Date::parse($this->request->input('date'))->unix();
        $this->eventSearchFields['ip'] = $this->request->input('ip', null);
        $this->eventSearchFields['user_status'] = $this->request->input('userStatus', null);
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
                'date' => ['required', 'date'],
                'ip' => ['ip'],
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
