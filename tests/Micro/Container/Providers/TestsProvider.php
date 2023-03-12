<?php

namespace Symbiotic\Tests\Micro\Container\Providers;

use Symbiotic\Core\ServiceProvider;
use Symbiotic\Tests\Micro\Container\Services\SingletonService;
use Symbiotic\Tests\Micro\Container\Services\SingletonServiceInterface;

class TestsProvider extends ServiceProvider
{
    public function register():void
    {
        $this->app->singleton(SingletonServiceInterface::class, function($app) {
            return new SingletonService($app, $app->get('test_data'));
        });
        $this->app->alias(SingletonServiceInterface::class, 'singleservice');
    }
}