<?php
declare(strict_types=1);

namespace IxocreateMigration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\CommonTypes\Entity\UuidType;

final class Version20180514112247 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('cms_sitemap');
        $table->addColumn('id', UuidType::serviceName());
        $table->addColumn('parentId', UuidType::serviceName())->setNotnull(false);
        $table->addColumn('nestedLeft', Type::INTEGER)->setNotnull(false);
        $table->addColumn('nestedRight', Type::INTEGER)->setNotnull(false);
        $table->addColumn('pageType', Type::STRING)->setLength(255);
        $table->addColumn('handle', Type::STRING)->setLength(255)->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["handle"]);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("cms_sitemap");
    }
}
