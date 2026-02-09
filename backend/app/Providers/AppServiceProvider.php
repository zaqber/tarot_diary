<?php

namespace App\Providers;

use App\Repositories\TagRepository;
use App\Repositories\TagRepositoryInterface;
use App\Repositories\TarotCardRepository;
use App\Repositories\TarotCardRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TarotCardRepositoryInterface::class,
            TarotCardRepository::class
        );

        $this->app->bind(
            TagRepositoryInterface::class,
            TagRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
