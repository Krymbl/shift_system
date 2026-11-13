<?php
declare(strict_types=1);

namespace App\Identity\Exception;

use App\Common\Exception\AbstractBusinessException;

final class UserChangeActiveStatusException extends AbstractBusinessException
{
    public function __construct(
        bool $isActive,
        ?\Throwable $previous = null
    )
    {
        $message = $isActive ? 'Вы не можете деактивировать пользователя, он уже деактивирован'
            : 'Вы не можете активировать пользователя, он уже активен';

        parent::__construct($message, $previous);
    }
}
