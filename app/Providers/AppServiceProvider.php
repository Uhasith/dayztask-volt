<?php

namespace App\Providers;

use App\Services\Task\TaskService;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentColor;
use App\Services\Notifications\NotificationService;
use Filament\Notifications\Livewire\DatabaseNotifications;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Amber,
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);

        DatabaseNotifications::trigger('filament.notifications.database-notifications-trigger');
        DatabaseNotifications::pollingInterval('30s');

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService;
        });

        $this->app->singleton(TaskService::class, function ($app) {
            return new TaskService;
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::macro('livewireCurrentRoute', function () {
            if (request()->route() && request()->route()->named('livewire.update')) {
                // Get the previous URL using the URL facade
                $previousUrl = URL::previous();

                // Match the previous URL to a route
                $previousRoute = app('router')->getRoutes()->match(
                    request()->create($previousUrl)
                );

                // Return the previous route name
                return optional($previousRoute)->getName();
            } else {
                // Return the current route name
                return optional(request()->route())->getName();
            }
        });
    }
}
