<?php

namespace App\Providers;

use App\Events\ReviewSaved;
use App\Listeners\RecalculateMovieRating;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ReviewSaved::class => [
            RecalculateMovieRating::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
