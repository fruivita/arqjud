<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/events
 * @see https://laravel.com/docs/9.x/scheduling#events
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
        Log::notice('ScheduledTaskStarting', [
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
        Log::notice('ScheduledTaskFinished', [
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
        Log::critical('ScheduledTaskFailed', [
            'expression' => $event->task->expression,
            'description' => $event->task->description,
            'exception' => $event->exception,
        ]);
    }

    /**
     * Register the listeners for the subscriber.
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
}
