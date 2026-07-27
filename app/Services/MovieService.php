<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\MovieData;
use App\DTO\MovieFilterData;
use App\Models\Movie;
use App\Repositories\Contracts\MovieRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MovieService
{
    private const CACHE_TTL_SECONDS = 300;

    public function __construct(private readonly MovieRepositoryInterface $movies)
    {
    }

    public function list(MovieFilterData $filters): LengthAwarePaginator
    {
        // Кэшируем только "холодные", часто повторяющиеся запросы первой страницы без поиска
        $isCacheable = $filters->search === null && $filters->genreId === null;

        if (! $isCacheable) {
            return $this->movies->paginate($filters);
        }

        $cacheKey = sprintf(
            'movies:list:%s:%s:%s:%d',
            $filters->sortBy,
            $filters->sortDirection,
            $filters->minRating ?? 'null',
            $filters->perPage,
        );

        return Cache::tags(['movies'])->remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            fn () => $this->movies->paginate($filters)
        );
    }

    public function findBySlug(string $slug): Movie
    {
        $movie = $this->movies->findBySlug($slug);

        abort_if($movie === null, 404, 'Фильм не найден');

        return $movie;
    }

    public function create(MovieData $data): Movie
    {
        $attributes = $data->toPersistArray();

        if ($data->poster) {
            $attributes['poster_path'] = Storage::disk('public')->put('posters', $data->poster);
        }

        $movie = $this->movies->create($attributes);

        if (!empty($data->genreIds)) {
            $this->movies->syncGenres($movie, $data->genreIds);
        }

        $this->flushListCache();

        return $movie->load('genres');
    }

    public function update(Movie $movie, MovieData $data): Movie
    {
        $attributes = $data->toPersistArray();

        if ($data->poster) {
            if ($movie->poster_path) {
                Storage::disk('public')->delete($movie->poster_path);
            }
            $attributes['poster_path'] = Storage::disk('public')->put('posters', $data->poster);
        }

        $movie = $this->movies->update($movie, $attributes);

        if (!empty($data->genreIds)) {
            $this->movies->syncGenres($movie, $data->genreIds);
        }

        $this->flushListCache();

        return $movie;
    }

    public function delete(Movie $movie): void
    {
        $this->movies->delete($movie);
        $this->flushListCache();
    }

    public function recalculateRating(int $movieId): void
    {
        $this->movies->recalculateRating($movieId);
        $this->flushListCache();
    }

    private function flushListCache(): void
    {
        Cache::tags(['movies'])->flush();
    }
}
