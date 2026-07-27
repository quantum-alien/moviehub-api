<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function __construct(private readonly Review $model)
    {
    }

    public function paginateForMovie(int $movieId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with('user:id,name')
            ->where('movie_id', $movieId)
            ->latest()
            ->paginate($perPage);
    }

    public function findByMovieAndUser(int $movieId, int $userId): ?Review
    {
        return $this->model->newQuery()
            ->where('movie_id', $movieId)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $attributes): Review
    {
        return $this->model->create($attributes);
    }

    public function update(Review $review, array $attributes): Review
    {
        $review->update($attributes);

        return $review->fresh();
    }

    public function delete(Review $review): bool
    {
        return (bool) $review->delete();
    }
}
