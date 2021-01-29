<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Schema\Type\UuidType;

final class Version20180514112247 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('cms_sitemap');
        $table->addColumn('id', UuidType::serviceName());
        $table->addColumn('parentId', UuidType::serviceName())->setNotnull(false);
        $table->addColumn('nestedLeft', Types::INTEGER)->setNotnull(false);
        $table->addColumn('nestedRight', Types::INTEGER)->setNotnull(false);
        $table->addColumn('pageType', Types::STRING)->setLength(255);
        $table->addColumn('handle', Types::STRING)->setLength(255)->setNotnull(false);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['handle']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('cms_sitemap');
    }
}
