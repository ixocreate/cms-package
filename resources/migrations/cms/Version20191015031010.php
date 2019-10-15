<?php declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Schema\Type\UuidType;

final class Version20191015031010 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_route_match');
        $table->addColumn("url", Type::STRING)->setLength(2048)->setCustomSchemaOption('collation', 'ascii_general_ci')->setCustomSchemaOption('charset', 'ascii');
        $table->addColumn("type", Type::STRING)->setLength(50);
        $table->addColumn("pageId", UuidType::serviceName());
        $table->addColumn("middleware", Type::JSON);
        $table->setPrimaryKey(["url"]);
        $table->addIndex(["pageId", "type"]);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_route_match");

    }
}
