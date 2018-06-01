<?php declare(strict_types=1);

namespace KiwiMigration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use KiwiSuite\CommonTypes\Entity\UuidType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180601120527 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_navigation');
        $table->addColumn('id', UuidType::class);
        $table->addColumn('pageId', UuidType::class);
        $table->addColumn('navigation', Type::STRING)->setLength(255);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(['pageId']);

    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_navigation");

    }
}
