<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\MovieData;
use App\DTO\MovieFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexMovieRequest;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Services\MovieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    public function __construct(private readonly MovieService $movieService)
    {
    }

    /**
     * @OA\Get(
     *     path="/movies",
     *     tags={"Movies"},
     *     summary="Список фильмов с фильтрацией и пагинацией",
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="genre_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="year_from", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="year_to", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="min_rating", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="sort_by", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Успешный ответ")
     * )
     */
    public function index(IndexMovieRequest $request): AnonymousResourceCollection
    {
        $filters = MovieFilterData::fromRequest($request->validated());

        return MovieResource::collection($this->movieService->list($filters));
    }

    /**
     * @OA\Get(
     *     path="/movies/{slug}",
     *     tags={"Movies"},
     *     summary="Детали фильма по slug",
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Успешный ответ"),
     *     @OA\Response(response=404, description="Фильм не найден")
     * )
     */
    public function show(string $slug): MovieResource
    {
        return new MovieResource($this->movieService->findBySlug($slug));
    }

    /**
     * @OA\Post(
     *     path="/movies",
     *     tags={"Movies"},
     *     summary="Создать фильм (только модератор/админ)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Фильм создан"),
     *     @OA\Response(response=403, description="Недостаточно прав")
     * )
     */
    public function store(StoreMovieRequest $request): JsonResponse
    {
        $data = MovieData::fromArray($request->validated(), $request->user()->id);

        $movie = $this->movieService->create($data);

        return (new MovieResource($movie))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/movies/{movie}",
     *     tags={"Movies"},
     *     summary="Обновить фильм (только модератор/админ)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Фильм обновлён")
     * )
     */
    public function update(UpdateMovieRequest $request, Movie $movie): MovieResource
    {
        $data = MovieData::fromArray($request->validated(), $movie->added_by);

        return new MovieResource($this->movieService->update($movie, $data));
    }

    /**
     * @OA\Delete(
     *     path="/movies/{movie}",
     *     tags={"Movies"},
     *     summary="Удалить фильм (только админ)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=204, description="Фильм удалён")
     * )
     */
    public function destroy(Movie $movie): JsonResponse
    {
        $this->authorize('delete', $movie);

        $this->movieService->delete($movie);

        return response()->json(null, 204);
    }
}
