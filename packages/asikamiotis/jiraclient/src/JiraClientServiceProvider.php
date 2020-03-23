<?php

namespace Asikamiotis\JiraClient;

use Illuminate\Support\ServiceProvider;


class JiraClientServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     */

    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */

    public function register()
    {
        $this->app->singleton(JiraClient::class, function () {
            return new JiraClient();
        });

        $this->app->alias(JiraClient::class, 'jira-client');
    }
}
