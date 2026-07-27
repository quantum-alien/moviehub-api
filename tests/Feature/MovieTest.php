<?php

use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function actingAsJwt(User $user): string
{
    return JWTAuth::fromUser($user);
}

it('отдаёт постраничный список фильмов', function () {
    Movie::factory()->count(20)->create();

    $response = $this->getJson('/api/v1/movies?per_page=5');

    $response->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'title', 'slug', 'avg_rating', 'genres']],
            'meta',
            'links',
        ]);
});

it('фильтрует фильмы по жанру', function () {
    $action = Genre::factory()->create(['name' => 'Боевик']);
    $drama = Genre::factory()->create(['name' => 'Драма']);

    $actionMovie = Movie::factory()->create();
    $actionMovie->genres()->attach($action->id);

    $dramaMovie = Movie::factory()->create();
    $dramaMovie->genres()->attach($drama->id);

    $response = $this->getJson("/api/v1/movies?genre_id={$action->id}");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.id'))->toBe($actionMovie->id);
});

it('возвращает 404 для несуществующего slug', function () {
    $this->getJson('/api/v1/movies/nonexistent-slug')->assertNotFound();
});

it('запрещает создание фильма неавторизованному пользователю', function () {
    $this->postJson('/api/v1/movies', ['title' => 'Test'])->assertUnauthorized();
});

it('запрещает создание фильма обычному пользователю', function () {
    $user = User::factory()->create();
    $token = actingAsJwt($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/movies', [
            'title' => 'Test Movie',
            'release_year' => 2024,
        ])
        ->assertForbidden();
});

it('позволяет модератору создать фильм', function () {
    $moderator = User::factory()->moderator()->create();
    $token = actingAsJwt($moderator);
    $genre = Genre::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/movies', [
            'title' => 'Новый фильм',
            'release_year' => 2024,
            'duration_minutes' => 120,
            'genre_ids' => [$genre->id],
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Новый фильм');

    $this->assertDatabaseHas('movies', ['title' => 'Новый фильм']);
});
