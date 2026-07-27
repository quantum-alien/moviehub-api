<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\ReviewData;
use App\Exceptions\ReviewAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Movie;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviewService)
    {
    }

    /**
     * @OA\Get(
     *     path="/movies/{movie}/reviews",
     *     tags={"Reviews"},
     *     summary="Список отзывов на фильм",
     *     @OA\Response(response=200, description="Успешный ответ")
     * )
     */
    public function index(Movie $movie): AnonymousResourceCollection
    {
        return ReviewResource::collection($this->reviewService->listForMovie($movie->id));
    }

    /**
     * @OA\Post(
     *     path="/movies/{movie}/reviews",
     *     tags={"Reviews"},
     *     summary="Оставить отзыв на фильм",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=201, description="Отзыв создан"),
     *     @OA\Response(response=409, description="Отзыв уже существует")
     * )
     */
    public function store(StoreReviewRequest $request, Movie $movie): JsonResponse
    {
        try {
            $data = ReviewData::fromArray($request->validated(), $movie->id, $request->user()->id);
            $review = $this->reviewService->create($data);
        } catch (ReviewAlreadyExistsException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return (new ReviewResource($review))->response()->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/reviews/{review}",
     *     tags={"Reviews"},
     *     summary="Обновить свой отзыв",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Отзыв обновлён")
     * )
     */
    public function update(StoreReviewRequest $request, Review $review): ReviewResource
    {
        $this->authorize('update', $review);

        $data = ReviewData::fromArray($request->validated(), $review->movie_id, $review->user_id);

        return new ReviewResource($this->reviewService->update($review, $data));
    }

    /**
     * @OA\Delete(
     *     path="/reviews/{review}",
     *     tags={"Reviews"},
     *     summary="Удалить отзыв (автор или модератор)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=204, description="Отзыв удалён")
     * )
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $this->reviewService->delete($review);

        return response()->json(null, 204);
    }
}
