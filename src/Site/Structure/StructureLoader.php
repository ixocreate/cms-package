<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Structure;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\StructureItemCacheable;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\SitemapRepository;

final class StructureLoader
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var StructureItemCacheable
     */
    private $structureItemCacheable;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(CacheManager $cacheManager, StructureItemCacheable $structureItemCacheable, SitemapRepository $sitemapRepository)
    {
        $this->cacheManager = $cacheManager;
        $this->structureItemCacheable = $structureItemCacheable;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function get(string $id, bool $force = false)
    {
        if ($id === "root") {
            return $this->loadRoot();
        }

        return $this->cacheManager->fetch(
            $this->structureItemCacheable->withId($id),
            $force
        );
    }

    public function loadRoot()
    {
        $dql = 'SELECT node.id
FROM ' . Sitemap::class . ' AS node
WHERE node.parentId IS NULL
ORDER BY node.nestedLeft';
        $query = $this->sitemapRepository->createQuery($dql);
        $result = $query->getArrayResult();

        $root = [];
        foreach ($result as $item) {
            $root[] = (string) $item['id'];
        }

        return [
            'sitemapId' => '',
            'handle' =>'',
            'pageType' => '',
            'pages' => [],
            'navigation' => [],
            'children' => $root,
            'level' => -1,
        ];
    }
}
