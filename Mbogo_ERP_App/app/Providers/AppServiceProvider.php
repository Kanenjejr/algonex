<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// 🔥 IMPORT MODELS + OBSERVERS
use App\Models\Delivery;
use App\Observers\DeliveryObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // 🔥 REGISTER OBSERVER
        Delivery::observe(DeliveryObserver::class);
    }
}