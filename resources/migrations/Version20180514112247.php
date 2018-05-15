<?php declare(strict_types=1);

namespace KiwiMigration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use KiwiSuite\CommonTypes\Entity\UuidType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180514112247 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_sitemap');
        $table->addColumn('id', UuidType::class);
        $table->addColumn('parentId', UuidType::class)->setNotnull(false);
        $table->addColumn('left', Type::INTEGER)->setNotnull(false);
        $table->addColumn('right', Type::INTEGER)->setNotnull(false);
        $table->addColumn('pageType', Type::STRING)->setLength(255);
        $table->addColumn('handle', Type::STRING)->setLength(255)->setNotnull(false);

        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["handle"]);

    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_sitemap");

    }
}
