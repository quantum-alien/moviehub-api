<?php

namespace App\Providers;

use App\Models\Movie;
use App\Models\Review;
use App\Policies\MoviePolicy;
use App\Policies\ReviewPolicy;
use App\Repositories\Contracts\MovieRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Eloquent\MovieRepository;
use App\Repositories\Eloquent\ReviewRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Биндинг интерфейсов репозиториев к их Eloquent-реализациям.
     * Позволяет подменять источник данных (например, для тестов) без изменения сервисов.
     */
    public array $bindings = [
        MovieRepositoryInterface::class => MovieRepository::class,
        ReviewRepositoryInterface::class => ReviewRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Movie::class, MoviePolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
    }
}
