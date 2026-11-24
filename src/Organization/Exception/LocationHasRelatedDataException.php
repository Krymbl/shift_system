<?php

declare(strict_types=1);

namespace App\Organization\Exception;

use App\Common\Exception\AbstractBusinessException;
use Symfony\Component\HttpFoundation\Response;

final class LocationHasRelatedDataException extends AbstractBusinessException
{
    protected const int STATUS_CODE = Response::HTTP_CONFLICT;

    public function __construct(
        ?\Throwable $previous = null
    ) {
        parent::__construct('Точка имеет связанные данные', $previous);
    }
}
