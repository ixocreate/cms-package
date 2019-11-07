<?php declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20191107051315 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('cms_sitemap');
        $table->addColumn('level', Type::INTEGER)->setNotnull(false);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
