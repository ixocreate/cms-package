<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Type\Entity\DateTimeType;
use Ixocreate\Type\Entity\UuidType;

final class Version20190228112949 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('cms_redirect_page');
        $table->addColumn('oldUrl', Type::STRING)->getLength(2048);
        $table->addColumn('pageId', UuidType::serviceName());
        $table->addColumn('createdAt', DateTimeType::serviceName());

        $table->setPrimaryKey(["oldUrl"]);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable("cms_redirect_page");
    }
}
