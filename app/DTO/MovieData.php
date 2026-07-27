<?php

namespace App\DTO;

use Illuminate\Http\UploadedFile;

final readonly class MovieData
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
    ) {
    }

    public static function fromArray(array $data, ?int $userId = null): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            releaseYear: (int) $data['release_year'],
            durationMinutes: isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
            director: $data['director'] ?? null,
            genreIds: $data['genre_ids'] ?? [],
            addedBy: $userId,
            poster: $data['poster'] ?? null,
        );
    }

    /**
     * Поля для персистентности (без отношений/файла).
     */
    public function toPersistArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'release_year' => $this->releaseYear,
            'duration_minutes' => $this->durationMinutes,
            'director' => $this->director,
            'added_by' => $this->addedBy,
        ], static fn ($value) => $value !== null);
    }
}
