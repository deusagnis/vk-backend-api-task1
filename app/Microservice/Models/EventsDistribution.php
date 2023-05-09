<?php

namespace App\Microservice\Models;

use Illuminate\Support\Facades\Date;
use MGGFLOW\LVMSVC\Models\Model;

/**
 * Модель для взаимодействия с данными Распределения Событий.
 */
class EventsDistribution extends Model
{
    protected $table = 'events_distribution';

    /**
     * Хэшировать строку в контексте События.
     * @param string $eventKey
     * @return string Хэш в бинарном представлении.
     */
    public static function hashEventKey(string $eventKey): string
    {
        return hash('fnv1a64', $eventKey, true);
    }

    /**
     * Генерировать метку даты по Unix метке времени.
     * @param int $unix
     * @return string
     */
    public static function genDateStamp(int $unix): string
    {
        return Date::createFromTimestamp($unix)->format('d-m-Y');
    }

    /**
     * Генерировать хэш События по названию и дате.
     * @param array $eventFields
     * @return string
     */
    public static function genCommonHash(array $eventFields): string
    {
        return self::hashEventKey(
            $eventFields['name'] . self::genDateStamp($eventFields['created_at'])
        );
    }

    /**
     * Генерировать хэш События по названию, дате, IP и статусу пользователя.
     * @param array $eventFields
     * @return string
     */
    public static function genExactHash(array $eventFields): string
    {
        return self::hashEventKey(
            $eventFields['name']
            . self::genDateStamp($eventFields['created_at'])
            . $eventFields['ip']
            . $eventFields['user_status']
        );
    }

    /**
     * Генерировать хэш События по названию, дате и IP.
     * @param array $eventFields
     * @return string
     */
    public static function genIpHash(array $eventFields): string
    {
        return self::hashEventKey(
            $eventFields['name']
            . self::genDateStamp($eventFields['created_at'])
            . $eventFields['ip']
        );
    }

    /**
     * Генерировать хэш события по названию, дате и статусу пользователя.
     * @param array $eventFields
     * @return string
     */
    public static function genUserStatusHash(array $eventFields): string
    {
        return self::hashEventKey(
            $eventFields['name']
            . self::genDateStamp($eventFields['created_at'])
            . $eventFields['user_status']
        );
    }
}
