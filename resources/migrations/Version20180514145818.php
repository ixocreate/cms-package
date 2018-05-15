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
final class Version20180514145818 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_page_version');
        $table->addColumn('id', UuidType::class);
        $table->addColumn('pageId', UuidType::class);
        $table->addColumn('content', Type::JSON);
        $table->addColumn('createdBy', UuidType::class)->setNotnull(false);
        $table->addColumn('approvedAt', DateTimeType::class)->setNotnull(false);
        $table->addColumn('createdAt', DateTimeType::class);

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["sitemapId", "locale"]);

    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_page_version");

    }
}
