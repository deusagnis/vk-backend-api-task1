<?php

namespace App\Microservice;

use App\Microservice\Models\EventsDistribution;

/**
 * Распределение События по счетчикам агрегации.
 * Вместо того чтобы в процессе запроса совершать выборку и подсчет элементов в таблице Событий напрямую,
 * используется предварительная подготовка необходимых счетчиков в дополнительной сущности Распределения Событий.
 *
 * Распределение Событий представляет собой хэш-таблицу параметров поиска и счетчиков.
 *
 */
class DistributeEvent
{
    private array $eventFields;

    private array $eventHashes;

    /**
     * Распределить событие по счетчикам.
     * @param array $eventFields
     * @return void
     */
    public function distribute(array $eventFields)
    {
        $this->eventFields = $eventFields;

        $this->genHashes();
        $this->provideDistribution();
    }

    private function provideDistribution()
    {
        foreach ($this->eventHashes as $eventHash) {
            if (EventsDistribution::query()->where('events_hash', $eventHash)->exists()) {
                EventsDistribution::query()->where('events_hash', $eventHash)->increment('counter');
            } else {
                EventsDistribution::query()->insert(['events_hash' => $eventHash, 'counter' => 1]);
            }
        }
    }

    private function genHashes()
    {
        $this->addHash(EventsDistribution::genCommonHash($this->eventFields));
        $this->addHash(EventsDistribution::genExactHash($this->eventFields));
        $this->addHash(EventsDistribution::genIpHash($this->eventFields));
        $this->addHash(EventsDistribution::genUserStatusHash($this->eventFields));
    }

    private function addHash(string $hash)
    {
        $this->eventHashes[] = $hash;
    }
}
