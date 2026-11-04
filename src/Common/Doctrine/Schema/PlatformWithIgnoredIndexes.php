<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\DBAL\Schema\SchemaManagerFactory;

final class PlatformWithIgnoredIndexes extends PostgreSQLPlatform implements SchemaManagerFactory
{
    public function createSchemaManager(Connection $connection): PostgreSQLSchemaManager
    {
        return new IgnoredIndexesSchemaManager($connection, $this);
    }
}
