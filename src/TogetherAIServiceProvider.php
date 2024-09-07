<?php

namespace ketchalegend\LaravelTogetherAI;

use Illuminate\Support\ServiceProvider;

class TogetherAIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/together-ai.php',
            'together-ai'
        );

        $this->app->singleton('together-ai', function ($app) {
            return new TogetherAI($app['config']['together-ai']);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/together-ai.php' => config_path('together-ai.php'),
            ], 'together-ai-config');
        }
    }
}
