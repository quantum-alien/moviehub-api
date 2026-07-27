<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Movie
 */
class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'release_year' => $this->release_year,
            'duration_minutes' => $this->duration_minutes,
            'director' => $this->director,
            'poster_url' => $this->poster_path ? asset('storage/'.$this->poster_path) : null,
            'avg_rating' => (float) $this->avg_rating,
            'reviews_count' => $this->reviews_count,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
