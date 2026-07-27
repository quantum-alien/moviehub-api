<?php

namespace App\Repositories\Eloquent;

use App\DTO\MovieFilterData;
use App\Models\Movie;
use App\Models\Review;
use App\Repositories\Contracts\MovieRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MovieRepository implements MovieRepositoryInterface
{
    private const ALLOWED_SORT_COLUMNS = ['created_at', 'release_year', 'avg_rating', 'title'];

    public function __construct(private readonly Movie $model)
    {
    }

    public function paginate(MovieFilterData $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('genres');

        if ($filters->search) {
            $query->where('title', 'ilike', "%{$filters->search}%");
        }

        if ($filters->genreId) {
            $query->whereHas('genres', fn ($q) => $q->where('genres.id', $filters->genreId));
        }

        if ($filters->yearFrom) {
            $query->where('release_year', '>=', $filters->yearFrom);
        }

        if ($filters->yearTo) {
            $query->where('release_year', '<=', $filters->yearTo);
        }

        if ($filters->minRating) {
            $query->where('avg_rating', '>=', $filters->minRating);
        }

        $sortColumn = in_array($filters->sortBy, self::ALLOWED_SORT_COLUMNS, true)
            ? $filters->sortBy
            : 'created_at';

        $sortDirection = strtolower($filters->sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $sortDirection)->paginate($filters->perPage);
    }

    public function findById(int $id): ?Movie
    {
        return $this->model->with('genres')->find($id);
    }

    public function findBySlug(string $slug): ?Movie
    {
        return $this->model->with('genres')->where('slug', $slug)->first();
    }

    public function create(array $attributes): Movie
    {
        return $this->model->create($attributes);
    }

    public function update(Movie $movie, array $attributes): Movie
    {
        $movie->update($attributes);

        return $movie->fresh('genres');
    }

    public function delete(Movie $movie): bool
    {
        return (bool) $movie->delete();
    }

    public function syncGenres(Movie $movie, array $genreIds): void
    {
        $movie->genres()->sync($genreIds);
    }

    public function recalculateRating(int $movieId): void
    {
        $stats = Review::query()
            ->where('movie_id', $movieId)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as reviews_count')
            ->first();

        $this->model->newQuery()->where('id', $movieId)->update([
            'avg_rating' => round((float) $stats->avg_rating, 2),
            'reviews_count' => (int) $stats->reviews_count,
        ]);
    }
}
