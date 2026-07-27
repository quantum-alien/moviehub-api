<?php

namespace App\Listeners;

use App\Events\ReviewSaved;
use App\Jobs\RecalculateMovieRatingJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecalculateMovieRating implements ShouldQueue
{
    public function handle(ReviewSaved $event): void
    {
        RecalculateMovieRatingJob::dispatch($event->movieId);
    }
}
