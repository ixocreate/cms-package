<?php declare(strict_types=1);

namespace IxocreateMigration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\CommonTypes\Entity\DateTimeType;
use Ixocreate\CommonTypes\Entity\UuidType;

final class Version20180514145006 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_page');
        $table->addColumn('id', UuidType::servicename());
        $table->addColumn('sitemapId', UuidType::servicename());
        $table->addColumn('locale', Type::STRING);
        $table->addColumn('name', Type::STRING)->setLength(255)->setNotnull(false);
        $table->addColumn('slug', Type::STRING)->setLength(255)->setNotnull(false);
        $table->addColumn('publishedFrom', DateTimeType::servicename())->setNotnull(false);
        $table->addColumn('publishedUntil', DateTimeType::servicename())->setNotnull(false);
        $table->addColumn('status', Type::STRING)->setLength(255);
        $table->addColumn('updatedAt', DateTimeType::servicename());
        $table->addColumn('createdAt', DateTimeType::servicename());

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["sitemapId", "locale"]);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_page");
    }
}
