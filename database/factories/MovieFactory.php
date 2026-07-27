<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MovieFactory extends Factory
{
    protected $model = Movie::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => fake()->paragraphs(3, true),
            'release_year' => fake()->numberBetween(1970, (int) date('Y')),
            'duration_minutes' => fake()->numberBetween(75, 210),
            'director' => fake()->name(),
            'avg_rating' => 0,
            'reviews_count' => 0,
        ];
    }
}
