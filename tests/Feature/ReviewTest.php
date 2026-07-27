<?php

use App\Jobs\RecalculateMovieRatingJob;
use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Tymon\JWTAuth\Facades\JWTAuth;

it('позволяет авторизованному пользователю оставить отзыв', function () {
    Queue::fake();

    $user = User::factory()->create();
    $movie = Movie::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/movies/{$movie->id}/reviews", [
            'rating' => 9,
            'comment' => 'Отличный фильм!',
        ]);

    $response->assertCreated()->assertJsonPath('data.rating', 9);

    $this->assertDatabaseHas('reviews', [
        'movie_id' => $movie->id,
        'user_id' => $user->id,
        'rating' => 9,
    ]);

    Queue::assertPushed(RecalculateMovieRatingJob::class);
});

it('запрещает оставить два отзыва на один фильм', function () {
    $user = User::factory()->create();
    $movie = Movie::factory()->create();
    Review::factory()->create(['movie_id' => $movie->id, 'user_id' => $user->id]);
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/movies/{$movie->id}/reviews", ['rating' => 5]);

    $response->assertStatus(409);
});

it('не позволяет удалить чужой отзыв', function () {
    $author = User::factory()->create();
    $intruder = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $author->id]);
    $token = JWTAuth::fromUser($intruder);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/reviews/{$review->id}")
        ->assertForbidden();
});

it('пересчитывает средний рейтинг фильма после джобы', function () {
    $movie = Movie::factory()->create(['avg_rating' => 0, 'reviews_count' => 0]);
    Review::factory()->create(['movie_id' => $movie->id, 'rating' => 8]);
    Review::factory()->create(['movie_id' => $movie->id, 'rating' => 4]);

    (new RecalculateMovieRatingJob($movie->id))->handle(app(\App\Services\MovieService::class));

    $movie->refresh();

    expect((float) $movie->avg_rating)->toBe(6.0)
        ->and($movie->reviews_count)->toBe(2);
});
