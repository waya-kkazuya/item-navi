<?php

namespace App\Providers;

use App\Models\Disposal;
use App\Models\Inspection;
use App\Models\Item;
use App\Observers\DisposalObserver;
use App\Observers\InspectionObserver;
use App\Observers\ItemObserver;
use Illuminate\Support\Facades\URL;
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

        // 本番環境でURLをHTTPSに強制する
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        } else {
            $this->app['request']->server->set('HTTPS', 'off');
        }
        
    }
}
