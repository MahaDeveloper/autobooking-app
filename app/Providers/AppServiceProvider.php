<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Interfaces\AuthenticateInterface;
use App\Services\AuthenticateService;
use App\Interfaces\UserRideInterface;
use App\Services\UserRideService;
use App\Interfaces\SupportInterface;
use App\Services\SupportService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(AuthenticateInterface::class, AuthenticateService::class);
        $this->app->bind(UserRideInterface::class, UserRideService::class);
        $this->app->bind(SupportInterface::class, SupportService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
