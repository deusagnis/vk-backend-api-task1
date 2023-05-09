<?php

namespace App\Microservice\Models;

use MGGFLOW\LVMSVC\Models\Model;

/**
 * Модель для взаимодействия с данными Событий.
 */
class Event extends Model
{
    /**
     * Значение статуса неавторизованного пользователя.
     */
    const USER_STATUS_UNAUTHORISED = 0;
    /**
     * Значение статуса авторизованного пользователя.
     */
    const USER_STATUS_AUTHORISED = 1;

    /**
     * Максимальная длина имени События.
     */
    const MAX_EVENT_NAME_LENGTH = 64;
}
