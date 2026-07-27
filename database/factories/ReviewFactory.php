<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'movie_id' => Movie::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 10),
            'comment' => fake()->boolean(70) ? fake()->paragraph() : null,
        ];
    }
}
