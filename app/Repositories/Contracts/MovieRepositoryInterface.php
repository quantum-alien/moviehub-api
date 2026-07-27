<?php

namespace App\Repositories\Contracts;

use App\DTO\MovieFilterData;
use App\Models\Movie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MovieRepositoryInterface
{
    public function paginate(MovieFilterData $filters): LengthAwarePaginator;

    public function findById(int $id): ?Movie;

    public function findBySlug(string $slug): ?Movie;

    public function create(array $attributes): Movie;

    public function update(Movie $movie, array $attributes): Movie;

    public function delete(Movie $movie): bool;

    public function syncGenres(Movie $movie, array $genreIds): void;

    public function recalculateRating(int $movieId): void;
}
