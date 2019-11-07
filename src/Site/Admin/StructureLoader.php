<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\StructureItemCacheable;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Site\Structure\StructureLoaderInterface;

final class StructureLoader implements StructureLoaderInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var bool
     */
    private $initialize = false;

    private $store = [];
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var StructureItemCacheable
     */
    private $structureItemCacheable;

    public function __construct(
        SitemapRepository $sitemapRepository,
        EntityManagerInterface $master,
        PageTypeSubManager $pageTypeSubManager,
        StructureItemCacheable $structureItemCacheable
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->entityManager = $master;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->structureItemCacheable = $structureItemCacheable;
    }

    public function get(string $id)
    {
        if ($id === "root") {
            return $this->loadRoot();
        }

        $this->initialize();

        if (isset($this->store[$id])) {
            return $this->store[$id];
        }

        $this->store[$id] = $this->structureItemCacheable->withId($id)->uncachedResult();
        return $this->store[$id];
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

    private function initialize()
    {
        if ($this->initialize === true) {
            return;
        }
        $this->initialize = true;
        $terminalPageTypeNames = [];
        foreach ($this->pageTypeSubManager->getServices() as $pageTypeClass) {
            if (\is_subclass_of($pageTypeClass, TerminalPageTypeInterface::class)) {
                $pageType = $this->pageTypeSubManager->get($pageTypeClass);
                $terminalPageTypeNames[] = $pageType->serviceName();
            }
        }

        $where = '';
        if (!empty($terminalPageTypeNames)) {
            $sql = 'SELECT id, nestedLeft, nestedRight FROM cms_sitemap WHERE pageType IN (\'' . \implode('\',\'', $terminalPageTypeNames) . '\')';
            $rm = new ResultSetMapping();
            $rm->addScalarResult('id', 'id', 'string');
            $rm->addScalarResult('nestedLeft', 'nestedLeft', 'integer');
            $rm->addScalarResult('nestedRight', 'nestedRight', 'integer');
            $query = $this->entityManager->createNativeQuery($sql, $rm);
            $result = $query->getResult();

            $nestedWhere = [];
            foreach ($result as $row) {
                $tmpLeft = $row['nestedLeft'] + 1;
                $nestedWhere[] = "(node.nestedLeft NOT BETWEEN {$tmpLeft} AND {$row['nestedRight']})";
            }

            if (!empty($nestedWhere)) {
                $where = 'WHERE ' . \implode(' AND ', $nestedWhere);
            }
        }

        $sql = "SELECT node.id, node.level, node.parentId, node.handle, node.pageType
FROM  cms_sitemap AS node {$where}
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

        $this->store = $flat;
    }
}
