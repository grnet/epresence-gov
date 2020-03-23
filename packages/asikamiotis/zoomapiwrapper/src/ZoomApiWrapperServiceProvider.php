<?php

namespace Asikamiotis\ZoomApiWrapper;

use Illuminate\Support\ServiceProvider;


class ZoomApiWrapperServiceProvider extends ServiceProvider
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
        $this->app->singleton(ZoomClient::class, function () {
            return new ZoomClient();
        });

        $this->app->alias(ZoomClient::class, 'zoom-client');
    }
}
