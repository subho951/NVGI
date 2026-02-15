<?php

namespace App\Providers;

use App\Services\CVParserService;
use Illuminate\Support\ServiceProvider;

class CVParserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CVParserService::class, function ($app) {
            return new CVParserService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
