<?php

namespace App\Providers;

use App\Services\MovieFinder\RemoteRepositoryInterface;
use App\Services\MovieFinder\OmdbRepository;
use App\Services\MovieFinder\AcademyRepository;
use Illuminate\Support\ServiceProvider;

/**
 * @psalm-api
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //$this->app->bind(RemoteRepositoryInterface::class, OmdbRepository::class);
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
