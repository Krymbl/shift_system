<?php

declare(strict_types=1);

namespace App\Identity\Exception;

use App\Common\Exception\AbstractBusinessException;
use Symfony\Component\HttpFoundation\Response;

final class PhoneAlreadyTakenException extends AbstractBusinessException
{
    protected const int STATUS_CODE = Response::HTTP_CONFLICT;

    public function __construct(
        ?\Throwable $previous = null
    )
    {
        parent::__construct('Пользователь с таким номером уже существует', $previous);
    }
}
