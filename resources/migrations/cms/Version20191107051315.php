<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20191107051315 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('cms_sitemap');
        $table->addColumn('level', Types::INTEGER)->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('cms_sitemap');
        $table->dropColumn('level');
    }
}
