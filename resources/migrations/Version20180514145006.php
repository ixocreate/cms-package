<?php declare(strict_types=1);

namespace KiwiMigration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use KiwiSuite\CommonTypes\Entity\DateTimeType;
use KiwiSuite\CommonTypes\Entity\UuidType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180514145006 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_page');
        $table->addColumn('id', UuidType::class);
        $table->addColumn('sitemapId', UuidType::class);
        $table->addColumn('locale', Type::STRING);
        $table->addColumn('name', Type::STRING)->setLength(255)->setNotnull(false);
        $table->addColumn('slug', Type::STRING)->setLength(255)->setNotnull(false);
        $table->addColumn('publishedFrom', DateTimeType::class)->setNotnull(false);
        $table->addColumn('publishedUntil', DateTimeType::class)->setNotnull(false);
        $table->addColumn('status', Type::STRING)->setLength(255);
        $table->addColumn('updatedAt', DateTimeType::class);
        $table->addColumn('createdAt', DateTimeType::class);

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["sitemapId", "locale"]);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_page");
    }
}
