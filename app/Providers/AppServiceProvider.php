<?php

namespace App\Providers;

use App\Models\Disposal;
use App\Models\Inspection;
use App\Models\Item;
use App\Observers\DisposalObserver;
use App\Observers\InspectionObserver;
use App\Observers\ItemObserver;
use Illuminate\Support\Facades\Gate;
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
        // Observer の登録
        Item::observe(ItemObserver::class);
        Inspection::observe(InspectionObserver::class);
        Disposal::observe(DisposalObserver::class);

        // Gate の定義（AuthServiceProvider から移行）
        Gate::define('guest', function ($user) {
            return $user->role === 0;
        });

        Gate::define('admin', function ($user) {
            return $user->role === 1;
        });

        Gate::define('staff-higher', function ($user) {
            return $user->role >= 0 && $user->role <= 5;
        });

        Gate::define('user-higher', function ($user) {
            return $user->role >= 0 && $user->role <= 9;
        });

        // Inertia エラー共有
        Inertia::share('errors', function () {
            return session()->get('errors') ? session()->get('errors')->getBag('default')->getMessages() : (object) [];
        });

        // 本番環境でURLをHTTPSを強制する
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
