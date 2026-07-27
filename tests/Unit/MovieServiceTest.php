<?php

use App\DTO\MovieData;
use App\Models\Movie;
use App\Repositories\Contracts\MovieRepositoryInterface;
use App\Services\MovieService;

it('создаёт фильм и привязывает жанры через репозиторий', function () {
    $movie = new Movie(['id' => 1, 'title' => 'Test']);

    $repository = Mockery::mock(MovieRepositoryInterface::class);
    $repository->shouldReceive('create')->once()->andReturn($movie);
    $repository->shouldReceive('syncGenres')->once()->with($movie, [1, 2]);

    $service = new MovieService($repository);

    $data = new MovieData(
        title: 'Test',
        description: null,
        releaseYear: 2024,
        durationMinutes: null,
        director: null,
        genreIds: [1, 2],
    );

    $service->create($data);
})->skip(fn () => ! class_exists(\Mockery::class), 'Mockery недоступен в окружении');
