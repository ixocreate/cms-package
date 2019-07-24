<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Single;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cache\CacheableInterface;

final class StructureCacheable implements CacheableInterface
{


    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @var
     */
    private $sitemapId;

    public function withSitemapId(string $sitemapId): StructureCacheable
    {
        $cacheable = clone $this;

        return $cacheable;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        $sql = "SELECT   n.id,
                COUNT(*)-1 AS level
            FROM cms_sitemap AS n,
                cms_sitemap AS p
            WHERE n.nestedLeft BETWEEN p.nestedLeft AND p.nestedRight
   			    AND
   		        n.id ='" . $this->sitemapId . "'	
            GROUP BY n.id
            ORDER BY n.nestedLeft";
        $rm = new ResultSetMapping();
        $rm->addScalarResult('id', 'id', 'string');
        $rm->addScalarResult('level', 'level', 'integer');
        $query = $this->entityManager->createNativeQuery($sql, $rm);
        $result = $query->execute(null, AbstractQuery::HYDRATE_SCALAR);
        if (\count($result) !== 1) {
            //TODO Exception
        }
        $level = $result[0]['level'];

        $pages = [];
        $sql = "SELECT id, locale FROM cms_page WHERE sitemapId = " . $this->sitemapId;
        $rm = new ResultSetMapping();
        $rm->addScalarResult('id', 'id', 'string');
        $rm->addScalarResult('locale', 'locale', 'string');
        $query = $this->entityManager->createNativeQuery($sql, $rm);
        $result = $query->execute(null, AbstractQuery::HYDRATE_SCALAR);
        foreach ($result as $item) {
            $pages[$item['locale']] = $item['id'];
        }

        $navigation = [];
        if (!empty($pages)) {
            $sql = "SELECT pageId, navigation FROM cms_navigation WHERE pageId IN (" . \implode(",", $pages) . ")";
            $rm = new ResultSetMapping();
            $rm->addScalarResult('pageId', 'pageId', 'string');
            $rm->addScalarResult('navigation', 'navigation', 'string');
            $query = $this->entityManager->createNativeQuery($sql, $rm);
            $result = $query->execute(null, AbstractQuery::HYDRATE_SCALAR);
            foreach ($result as $item) {
                $locale = \array_search($item['pageId'], $pages);
                if (empty($locale)) {
                    continue;
                }
                if (!\array_key_exists($locale, $navigation)) {
                    $navigation[$locale] = [];
                }

                $navigation[$locale][] = $item['navigation'];
            }
        }


        $children = [];
        $sql = "SELECT id FROM cms_sitemap WHERE parentId = " . $this->sitemapId . " ORDER BY nestedLeft";
        $rm = new ResultSetMapping();
        $rm->addScalarResult('id', 'id', 'string');
        $query = $this->entityManager->createNativeQuery($sql, $rm);
        $result = $query->execute(null, AbstractQuery::HYDRATE_SCALAR);
        foreach ($result as $item) {
            $children[] = $item['id'];
        }

        return Structure::prepare(
            $this->sitemapId,
            $pages,
            $navigation,
            $children,
            $level
        );
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {

    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'structure.' . $this->sitemapId;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 2592000;
    }
}
