<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/events
 * @see https://laravel.com/docs/scheduling#events
 */
class ScheduledEventSubscriber
{
    /**
     * Handle Scheduled Task Starting events.
     *
     * @return void
     */
    public function handleScheduledTaskStarting(ScheduledTaskStarting $event)
    {
        $this->log('notice', 'ScheduledTaskStarting', [
            'expression' => $event->task->expression,
            'description' => $event->task->description,
        ]);
    }

    /**
     * Handle Scheduled Task Finished events.
     *
     * @return void
     */
    public function handleScheduledTaskFinished(ScheduledTaskFinished $event)
    {
        $this->log('notice', 'ScheduledTaskFinished', [
            'expression' => $event->task->expression,
            'description' => $event->task->description,
        ]);
    }

    /**
     * Handle Scheduled Task Failed events.
     *
     * @return void
     */
    public function handleScheduledTaskFailed(ScheduledTaskFailed $event)
    {
        $this->log('critical', 'ScheduledTaskFailed', [
            'expression' => $event->task->expression,
            'description' => $event->task->description,
            'exception' => $event->exception,
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     *
     * @return void
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            ScheduledTaskStarting::class,
            [ScheduledEventSubscriber::class, 'handleScheduledTaskStarting']
        );

        $events->listen(
            ScheduledTaskFinished::class,
            [ScheduledEventSubscriber::class, 'handleScheduledTaskFinished']
        );

        $events->listen(
            ScheduledTaskFailed::class,
            [ScheduledEventSubscriber::class, 'handleScheduledTaskFailed']
        );
    }

    /**
     * Loga com um nível arbitrário.
     *
     * A mensagem PRECISA ser uma string ou um objeto que implemente
     * __toString().
     *
     * A mensagem PODE conter placeholders no formato: {foo} em que 'foo' será
     * substituído pelo dado de contexto da chave 'foo'.
     *
     * O array com os dados de contexto pode ter dados arbitrários. A única
     * presunção que deve ser levada em consideração é que se uma instância de
     * Exception for informada para se produzir o stack trace, ela DEVERÁ estar
     * na chave de nome 'exception'.
     *
     * @param string               $level   nível do log
     * @param string|\Stringable   $message sobre o ocorrido
     * @param array<string, mixed> $context dados de contexto
     *
     * @return void
     *
     * @see https://www.php-fig.org/psr/psr-3/
     * @see https://www.php.net/manual/en/function.array-merge.php
     */
    private function log(string $level, string|\Stringable $message, array $context = [])
    {
        Log::log($level, $message, $context);
    }
}
