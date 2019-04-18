<?php
declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Package\Type\Entity\DateTimeType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181019082844 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('cms_page');
        $table->addColumn('releasedAt', DateTimeType::serviceName())->setNotnull(false);

        $table->addIndex(['releasedAt']);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('cms_page');
        $table->dropColumn('releasedAt');
    }
}
