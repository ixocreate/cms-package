<?php declare(strict_types=1);

namespace IxocreateMigration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\CommonTypes\Entity\DateTimeType;
use Ixocreate\CommonTypes\Entity\SchemaType;
use Ixocreate\CommonTypes\Entity\UuidType;

final class Version20180514145818 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_page_version');
        $table->addColumn('id', UuidType::servicename());
        $table->addColumn('pageId', UuidType::servicename());
        $table->addColumn('content', SchemaType::servicename());
        $table->addColumn('createdBy', UuidType::servicename())->setNotnull(false);
        $table->addColumn('approvedAt', DateTimeType::servicename())->setNotnull(false);
        $table->addColumn('createdAt', DateTimeType::servicename());

        $table->setPrimaryKey(["id"]);
        $table->addIndex(['pageId', 'approvedAt']);
        $table->addIndex(['pageId', 'createdAt']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_page_version");
    }
}
