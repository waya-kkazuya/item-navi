<?php

namespace App\Providers;

use App\Models\Item;
use App\Models\Inspection;
use App\Models\Disposal;
use App\Observers\ItemObserver;
use App\Observers\InspectionObserver;
use App\Observers\DisposalObserver;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Item::observe(ItemObserver::class);
        Inspection::observe(InspectionObserver::class);
        Disposal::observe(DisposalObserver::class);
        Inertia::share('errors', function () {
            return session()->get('errors') ? session()->get('errors')->getBag('default')->getMessages() : (object) [];
        });
    }
}
