<?php

namespace Huozi\ImageFactory;

use Huozi\ImageFactory\ImageManager;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{

    public function register()
    {
        $this->app->singleton(ImageManager::class, function ($app) {
            return new ImageManager($app);
        });
    }

    public function boot()
    {
        ImageManager::extendDefaultDrivers();
    }

}
