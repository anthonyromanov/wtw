<?php

namespace App\Providers;

use App\Services\MovieService\RemoteRepositoryInterface;
use App\Services\MovieService\OmdbRepository;
use App\Services\MovieService\AcademyRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind(RemoteRepositoryInterface::class, OmdbRepository::class);
        $this->app->bind(RemoteRepositoryInterface::class, AcademyRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
