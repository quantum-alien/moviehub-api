<?php

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function paginateForMovie(int $movieId, int $perPage = 15): LengthAwarePaginator;

    public function findByMovieAndUser(int $movieId, int $userId): ?Review;

    public function create(array $attributes): Review;

    public function update(Review $review, array $attributes): Review;

    public function delete(Review $review): bool;
}
