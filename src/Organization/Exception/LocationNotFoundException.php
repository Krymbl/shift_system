<?php

declare(strict_types=1);

namespace App\Organization\Exception;

use App\Common\Exception\AbstractBusinessException;
use Symfony\Component\HttpFoundation\Response;

final class LocationNotFoundException extends AbstractBusinessException
{
    protected const int STATUS_CODE = Response::HTTP_NOT_FOUND;

    public function __construct(
        string $message = 'Точка не найдена',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $previous);
    }
}
