<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Favorite;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/favorites",
     *     tags={"Favorites"},
     *     summary="Список избранных фильмов текущего пользователя",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Успешный ответ")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $movies = Movie::query()
            ->whereHas('favorites', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('genres')
            ->paginate(15);

        return MovieResource::collection($movies);
    }

    /**
     * @OA\Post(
     *     path="/movies/{movie}/favorite",
     *     tags={"Favorites"},
     *     summary="Добавить фильм в избранное",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Добавлено")
     * )
     */
    public function store(Request $request, Movie $movie): JsonResponse
    {
        Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'movie_id' => $movie->id,
        ]);

        return response()->json(['message' => 'Фильм добавлен в избранное'], 201);
    }

    /**
     * @OA\Delete(
     *     path="/movies/{movie}/favorite",
     *     tags={"Favorites"},
     *     summary="Удалить фильм из избранного",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=204, description="Удалено")
     * )
     */
    public function destroy(Request $request, Movie $movie): JsonResponse
    {
        Favorite::where('user_id', $request->user()->id)
            ->where('movie_id', $movie->id)
            ->delete();

        return response()->json(null, 204);
    }
}
