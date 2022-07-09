<?php

namespace App\Enums;

/*
 * Lista de queues presentes na aplicação.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 */
enum Queue: string
{
    case Corporativo = 'corporativo';
    /**
     * Todos os valores possíveis deste enum.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function valores()
    {
        return
        collect(Queue::cases())
        ->transform(function ($queue) {
            return $queue->value;
        });
    }
}
