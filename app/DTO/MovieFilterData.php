<?php

namespace App\DTO;

final readonly class MovieFilterData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $releaseYear,
        public ?int $durationMinutes,
        public ?string $director,
        public array $genreIds,
        public ?int $addedBy = null,
        public ?UploadedFile $poster = null,
    ) {}

    public static function fromRequest(array $query): self
    {
        return new self(
            search: $query['search'] ?? null,
            genreId: isset($query['genre_id']) ? (int) $query['genre_id'] : null,
            yearFrom: isset($query['year_from']) ? (int) $query['year_from'] : null,
            yearTo: isset($query['year_to']) ? (int) $query['year_to'] : null,
            minRating: isset($query['min_rating']) ? (float) $query['min_rating'] : null,
            sortBy: $query['sort_by'] ?? 'created_at',
            sortDirection: $query['sort_direction'] ?? 'desc',
            perPage: min((int) ($query['per_page'] ?? 15), 100),
        );
    }
}
