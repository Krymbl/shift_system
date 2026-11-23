<?php

declare(strict_types=1);

namespace App\Organization\Exception;

use App\Common\Exception\AbstractBusinessException;
use Symfony\Component\HttpFoundation\Response;

final class EmployeeNotFoundException extends AbstractBusinessException
{
    protected const int STATUS_CODE = Response::HTTP_NOT_FOUND;

    public function __construct(
        int $id,
        ?\Throwable $previous = null
    ) {
        parent::__construct("Сотрудник с id: $id не найден", $previous);
    }
}
