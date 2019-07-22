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
