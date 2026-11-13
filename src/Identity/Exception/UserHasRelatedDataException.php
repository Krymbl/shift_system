<?php
declare(strict_types=1);

namespace App\Identity\Exception;

use App\Common\Exception\AbstractBusinessException;
use Symfony\Component\HttpFoundation\Response;

final class UserHasRelatedDataException extends AbstractBusinessException
{

    protected const int STATUS_CODE = Response::HTTP_CONFLICT;

    public function __construct(
        int         $userId,
        ?\Throwable $previous = null
    ) {
        parent::__construct("У пользователя id: $userId есть данные, его можно только деактивировать", $previous);
    }

}
