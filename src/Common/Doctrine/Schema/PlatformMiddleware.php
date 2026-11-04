<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Schema;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

final class PlatformMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new PlatformDriver($driver);
    }
}
