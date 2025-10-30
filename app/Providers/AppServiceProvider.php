<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Http\Guards\AdminGuard;

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
        // Register custom admin guard
        Auth::extend('admin', function ($app, $name, $config) {
            $guard = new AdminGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store'],
                $app['request']
            );

            // Wire up CookieJar and Event Dispatcher like Laravel's default SessionGuard
            $guard->setCookieJar($app['cookie']);
            $guard->setDispatcher($app['events']);

            // Ensure the guard's request instance stays in sync with the container
            $app->refresh('request', $guard, 'setRequest');

            // Align remember duration with session lifetime
            $guard->setRememberDuration(config('session.lifetime') * 60);

            return $guard;
        });
    }
}
