<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191107051316 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = "UPDATE cms_sitemap as s INNER JOIN
(SELECT n.id,
         n.nestedLeft,
         COUNT(*)-1 AS level,
         ROUND ((n.nestedRight - n.nestedLeft - 1) / 2) AS offspring
    FROM cms_sitemap AS n,
         cms_sitemap AS p
   WHERE (n.nestedLeft BETWEEN p.nestedLeft AND p.nestedRight)
GROUP BY n.id, n.nestedLeft
ORDER BY n.nestedLeft) as sub ON (s.id = sub.id)
SET s.level=sub.level";
        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
