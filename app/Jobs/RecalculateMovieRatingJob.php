<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\MovieService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateMovieRatingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(private readonly int $movieId)
    {
    }

    public function handle(MovieService $movieService): void
    {
        $movieService->recalculateRating($this->movieId);
    }
}
