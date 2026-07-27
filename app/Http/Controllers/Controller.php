<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     title="MovieHub API",
 *     version="1.0.0",
 *     description="REST API сервиса просмотра фильмов: каталог, жанры, отзывы, избранное."
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\Server(url="/api/v1", description="MovieHub API v1")
 */
abstract class Controller
{
    use AuthorizesRequests;
}
