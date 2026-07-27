<?php

namespace App\Exceptions;

use Exception;

class ReviewAlreadyExistsException extends Exception
{
    protected $message = 'Вы уже оставили отзыв на этот фильм. Используйте обновление отзыва.';

    protected $code = 409;
}
