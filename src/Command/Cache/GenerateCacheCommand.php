<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Command\Structure;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cache\CacheSubManager;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Cacheable\StructureItemCacheable;
use Ixocreate\CommandBus\Command\AbstractCommand;

final class GenerateCacheCommand extends AbstractCommand
{
    /**
     * @var SitemapCacheable
     */
    private $sitemapCacheable;

    /**
     * @var StructureItemCacheable
     */
    private $structureItemCacheable;

    /**
     * @var CacheSubManager
     */
    private $cacheSubManager;

    public function __construct(
        EntityManagerInterface $master,
        SitemapCacheable $sitemapCacheable,
        StructureItemCacheable $structureItemCacheable,
        CacheSubManager $cacheSubManager
    ) {
        $this->entityManager = $master;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->structureItemCacheable = $structureItemCacheable;
        $this->cacheSubManager = $cacheSubManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $sql = "SELECT node.id, node.level, node.parentId, node.handle, node.pageType
FROM  cms_sitemap AS node
ORDER BY node.nestedLeft";

        $rm = new ResultSetMapping();
        $rm->addScalarResult('id', 'id', 'string');
        $rm->addScalarResult('parentId', 'parentId', 'string');
        $rm->addScalarResult('pageType', 'pageType', 'string');
        $rm->addScalarResult('handle', 'handle', 'string');
        $rm->addScalarResult('level', 'level', 'integer');

        $query = $this->entityManager->createNativeQuery($sql, $rm);

        $result = $query->iterate(null, Query::HYDRATE_OBJECT);

        $flat = [];
        $root = [];
        foreach ($result as $item) {
            if (empty($item)) {
                continue;
            }
            $item = \current($item);

            $flat[$item['id']] = [
                'sitemapId' => $item['id'],
                'handle' => $item['handle'],
                'pageType' => $item['pageType'],
                'level' => $item['level'],
                'pages' => [],
                'navigation' => [],
                'children' => [],
            ];

            if (!empty($item['parentId'])) {
                $flat[$item['parentId']]['children'][] = $item['id'];
            } else {
                $root[] = $item['id'];
            }
        }
        unset($result);

        if (!empty($flat)) {
            $sql = "SELECT p.id,
                        p.sitemapId,
                        p.locale
                    FROM cms_page p
                    WHERE p.sitemapId IN (SELECT s.id FROM cms_sitemap s)";

            $rm = new ResultSetMapping();
            $rm->addScalarResult('id', 'id', 'string');
            $rm->addScalarResult('sitemapId', 'sitemapId', 'string');
            $rm->addScalarResult('locale', 'locale', 'string');

            $query = $this->entityManager->createNativeQuery($sql, $rm);
            $result = $query->iterate(null, Query::HYDRATE_OBJECT);

            foreach ($result as $item) {
                if (empty($item)) {
                    continue;
                }
                $item = \current($item);

                if (!\array_key_exists($item['sitemapId'], $flat)) {
                    continue;
                }

                $flat[$item['sitemapId']]['pages'][$item['locale']] = $item['id'];
            }

            unset($result);

            $sql = "SELECT s.id, n.pageId, n.navigation
                FROM cms_navigation n
                INNER JOIN cms_page p ON (n.pageId = p.id)
                INNER JOIN cms_sitemap s ON (p.sitemapId = s.id)";
            $rm = new ResultSetMapping();
            $rm->addScalarResult('id', 'id', 'string');
            $rm->addScalarResult('pageId', 'pageId', 'string');
            $rm->addScalarResult('navigation', 'navigation', 'string');

            $query = $this->entityManager->createNativeQuery($sql, $rm);
            $result = $query->iterate(null, Query::HYDRATE_OBJECT);

            foreach ($result as $item) {
                if (empty($item)) {
                    continue;
                }
                $item = \current($item);

                $flat[$item['id']]['navigation'][$item['pageId']][] = $item['navigation'];
            }

            unset($result);
        }

        foreach ($flat as $key => $item) {
            $cachable = $this->structureItemCacheable->withId($key);

            $this->cacheSubManager->get('cms_store')->put(
                $cachable->cacheKey(),
                $item,
                $cachable->cacheTtl()
            );

            unset($flat[$key]);
        }

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms-cache-generate';
    }
}
