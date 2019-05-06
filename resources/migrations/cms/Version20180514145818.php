<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\SchemaType;
use Ixocreate\Schema\Type\UuidType;

final class Version20180514145818 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('cms_page_version');
        $table->addColumn('id', UuidType::serviceName());
        $table->addColumn('pageId', UuidType::serviceName());
        $table->addColumn('content', SchemaType::serviceName());
        $table->addColumn('createdBy', UuidType::serviceName())->setNotnull(false);
        $table->addColumn('approvedAt', DateTimeType::serviceName())->setNotnull(false);
        $table->addColumn('createdAt', DateTimeType::serviceName());

        $table->setPrimaryKey(["id"]);
        $table->addIndex(['pageId', 'approvedAt']);
        $table->addIndex(['pageId', 'createdAt']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("cms_page_version");
    }
}
