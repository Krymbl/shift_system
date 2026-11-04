<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Schema;

use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\ServerVersionProvider;

final class PlatformDriver extends AbstractDriverMiddleware
{
    public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
    {
        return new PlatformWithIgnoredIndexes();
    }
}
