<?php

declare(strict_types=1);

namespace App\Common\Exception;

abstract class AbstractAppException extends \RuntimeException
{
    public function __construct(
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0,  $previous);
    }
}
