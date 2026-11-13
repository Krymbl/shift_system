<?php

declare(strict_types=1);

namespace App\Identity\Exception;

use App\Common\Exception\AbstractBusinessException;
use Symfony\Component\HttpFoundation\Response;

final class UserNotFoundException extends AbstractBusinessException
{
    protected const int STATUS_CODE = Response::HTTP_NOT_FOUND;

    public function __construct(
        int $userId,
        ?\Throwable $previous = null
    ) {
        parent::__construct("Пользователь с id: $userId не найден", $previous);
    }
}
