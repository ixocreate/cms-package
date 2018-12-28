<?php declare(strict_types=1);

namespace IxocreateMigration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\CommonTypes\Entity\UuidType;

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
