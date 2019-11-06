<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Database\Tree\TreeRepository;

final class SitemapRepository extends TreeRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Sitemap::class;
    }

    public function level($id): int
    {
        $dql = 'SELECT node.id, (COUNT(parent.id) - 1) AS level
FROM ' . Sitemap::class . ' AS node,
        ' . Sitemap::class . ' AS parent
WHERE node.nestedLeft BETWEEN parent.nestedLeft AND parent.nestedRight AND node.id=:id
GROUP BY node.id
ORDER BY node.nestedLeft';

        $query = $this->createQuery($dql);
        $query->setParameter('id', (string) $id);

        $result = $query->getSingleResult();
        if (empty($result)) {
            return 0;
        }

        return (int) $result['level'];
    }

    public function receiveUsedHandles(): array
    {
        $handles = [];
        $result = $this->createQuery("SELECT s FROM " . Sitemap::class . " s WHERE s.handle IS NOT NULL")->execute();
        foreach ($result as $sitemap) {
            $handles[] = $sitemap->handle();
        }
        return $handles;
    }
}
