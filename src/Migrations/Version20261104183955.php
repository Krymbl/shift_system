<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261104183955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Комментарии product';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN product.article IS \'Артикул (уникальный идентификатор)\'');
        $this->addSql('COMMENT ON COLUMN product.price IS \'Цена копейки\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN product.article IS \'Артикул (уникальный идентиикатор)\'');
        $this->addSql('COMMENT ON COLUMN product.price IS \'Цена\'');
    }
}
