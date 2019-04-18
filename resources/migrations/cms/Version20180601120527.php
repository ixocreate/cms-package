<?php
declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Type\Entity\UuidType;

final class Version20180601120527 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('cms_navigation');
        $table->addColumn('id', UuidType::serviceName());
        $table->addColumn('pageId', UuidType::serviceName());
        $table->addColumn('navigation', Type::STRING)->setLength(255);

        $table->setPrimaryKey(["id"]);
        $table->addIndex(['pageId']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("cms_navigation");
    }
}
