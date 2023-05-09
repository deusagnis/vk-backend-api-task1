<?php

namespace App\Microservice\Controllers;

use App\Http\Controllers\Controller;
use App\Microservice\Facades\EventAdding;
use App\Microservice\Facades\EventDistributionSearch;
use Illuminate\Http\Request;
use MGGFLOW\ExceptionManager\Interfaces\UniException;

/**
 * Контроллер для работы с Событиями.
 */
class EventController extends Controller
{
    /**
     * Добавить Событие.
     *
     * @param Request $request
     * @return array
     * @throws UniException
     */
    public function add(Request $request): array
    {
        $result = [];

        $adding = new EventAdding($request);
        $result['result'] = $adding->add();


        return $result;
    }

    /**
     * Получить счетчик для выборки Событий.
     * @param Request $request
     * @return array
     * @throws UniException
     */
    public function stat(Request $request): array
    {
        $result = [];

        $search = new EventDistributionSearch($request);
        $result['result'] = $search->find();

        return $result;
    }
}
