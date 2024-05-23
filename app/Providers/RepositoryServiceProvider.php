<?php

namespace App\Providers;

use App\Repositories\ProjectRepository;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\ProjectRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
