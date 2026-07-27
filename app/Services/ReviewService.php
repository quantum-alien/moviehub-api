<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ReviewData;
use App\Events\ReviewSaved;
use App\Exceptions\ReviewAlreadyExistsException;
use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function __construct(private readonly ReviewRepositoryInterface $reviews)
    {
    }

    public function listForMovie(int $movieId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->reviews->paginateForMovie($movieId, $perPage);
    }

    public function create(ReviewData $data): Review
    {
        $existing = $this->reviews->findByMovieAndUser($data->movieId, $data->userId);

        if ($existing !== null) {
            throw new ReviewAlreadyExistsException;
        }

        $review = $this->reviews->create($data->toPersistArray());

        event(new ReviewSaved($review->movie_id));

        return $review;
    }

    public function update(Review $review, ReviewData $data): Review
    {
        $review = $this->reviews->update($review, [
            'rating' => $data->rating,
            'comment' => $data->comment,
        ]);

        event(new ReviewSaved($review->movie_id));

        return $review;
    }

    public function delete(Review $review): void
    {
        $movieId = $review->movie_id;

        $this->reviews->delete($review);

        event(new ReviewSaved($movieId));
    }
}
