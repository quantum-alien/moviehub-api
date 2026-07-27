<?php

use App\DTO\MovieFilterData;

it('строит DTO из query-параметров с дефолтами', function () {
    $dto = MovieFilterData::fromRequest([]);

    expect($dto->sortBy)->toBe('created_at')
        ->and($dto->sortDirection)->toBe('desc')
        ->and($dto->perPage)->toBe(15)
        ->and($dto->search)->toBeNull();
});

it('ограничивает per_page максимумом в 100', function () {
    $dto = MovieFilterData::fromRequest(['per_page' => 500]);

    expect($dto->perPage)->toBe(100);
});

it('корректно приводит типы параметров', function () {
    $dto = MovieFilterData::fromRequest([
        'genre_id' => '3',
        'min_rating' => '7.5',
        'year_from' => '2000',
    ]);

    expect($dto->genreId)->toBe(3)
        ->and($dto->minRating)->toBe(7.5)
        ->and($dto->yearFrom)->toBe(2000);
});
