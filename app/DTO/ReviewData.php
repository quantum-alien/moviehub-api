<?php

namespace App\DTO;

final readonly class ReviewData
{
    public function __construct(
        public int $movieId,
        public int $userId,
        public int $rating,
        public ?string $comment,
    ) {
    }

    public static function fromArray(array $data, int $movieId, int $userId): self
    {
        return new self(
            movieId: $movieId,
            userId: $userId,
            rating: (int) $data['rating'],
            comment: $data['comment'] ?? null,
        );
    }

    public function toPersistArray(): array
    {
        return [
            'movie_id' => $this->movieId,
            'user_id' => $this->userId,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ];
    }
}
