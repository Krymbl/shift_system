<?php

declare(strict_types=1);

namespace App\Common\Exception;

use Symfony\Component\HttpFoundation\Response;

abstract class AbstractBusinessException extends \Exception
{
    protected const int STATUS_CODE = Response::HTTP_BAD_REQUEST;

    public function __construct(
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return static::STATUS_CODE;
    }
}
