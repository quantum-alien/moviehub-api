<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GenreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/genres",
     *     tags={"Genres"},
     *     summary="Список всех жанров",
     *     @OA\Response(response=200, description="Успешный ответ")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $genres = Cache::remember('genres:all', 3600, fn () => Genre::orderBy('name')->get());

        return GenreResource::collection($genres);
    }
}
