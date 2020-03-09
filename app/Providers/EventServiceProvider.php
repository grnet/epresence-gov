<?php

namespace App\Providers;

use App\Observers\AuditLogObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\AuditLog;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ParticipantStatusChanged' => [
            'App\Listeners\ParticipantStatusChangedListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        AuditLog::observe(AuditLogObserver::class);
        //
    }

    protected $subscribe = [
        'App\Listeners\ConferenceEventSubscriber',
    ];
}
