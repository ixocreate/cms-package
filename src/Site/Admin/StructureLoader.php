<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cms\Cacheable\StructureItemCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Router\PageRoute;
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
    private $store2= [];

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var StructureItemCacheable
     */
    private $structureItemCacheable;
    /**
     * @var PageRoute
     */
    private $pageRoute;

    public function __construct(
        SitemapRepository $sitemapRepository,
        EntityManagerInterface $master,
        PageTypeSubManager $pageTypeSubManager,
        StructureItemCacheable $structureItemCacheable,
        PageRoute $pageRoute
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->entityManager = $master;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->structureItemCacheable = $structureItemCacheable;
        $this->pageRoute = $pageRoute;
    }

    public function getTree(?string $handle = null) {
        $this->initialize($handle);
        return $this->store2;
    }

    public function get(string $id)
    {
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
            'handle' => '',
            'pageType' => '',
            'pages' => [],
            'navigation' => [],
            'children' => $root,
            'level' => -1,
        ];
    }

    private function initialize(?string $handle = null)
    {
        if ($this->initialize === true) {
            return;
        }
        $this->initialize = true;

        $pageTypes = [];
        $terminalPageTypeNames = [];
        foreach ($this->pageTypeSubManager->services() as $pageTypeClass) {
            $pageType = $this->pageTypeSubManager->get($pageTypeClass);
            $pageTypes[$pageType::serviceName()] = [
                'label' => $pageType->label(),
                'allowedChildren' => $pageType->allowedChildren(),
                'isRoot' => $pageType instanceof RootPageTypeInterface,
                'name' => $pageType::serviceName(),
                'terminal' => $pageType instanceof TerminalPageTypeInterface,
            ];
            if (\is_subclass_of($pageTypeClass, TerminalPageTypeInterface::class)) {
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

        $sql = "SELECT node.id, node.parentId, node.handle, node.pageType, node.nestedLeft, node.nestedRight, node.level
FROM cms_sitemap AS node {$where}
ORDER BY node.nestedLeft";

        $rm = new ResultSetMapping();
        $rm->addScalarResult('id', 'id', 'string');
        $rm->addScalarResult('parentId', 'parentId', 'string');
        $rm->addScalarResult('pageType', 'pageType', 'string');
        $rm->addScalarResult('handle', 'handle', 'string');
        $rm->addScalarResult('level', 'level', 'integer');
        $rm->addScalarResult('nestedLeft', 'nestedLeft', 'integer');
        $rm->addScalarResult('nestedRight', 'nestedRight', 'integer');
        $rm->addScalarResult('level', 'level', 'integer');

        $query = $this->entityManager->createNativeQuery($sql, $rm);
        $result = $query->toIterable([], Query::HYDRATE_OBJECT);

        $flat = [];
        $root = [];
        foreach ($result as $item) {
            if (empty($item)) {
                continue;
            }

            $flat[$item['id']] = [
                'sitemap' => [
                    'id' => $item['id'],
                    'parentId' => $item['parentId'],
                    'nestedLeft' => $item['nestedLeft'],
                    'nestedRight' => $item['nestedRight'],
                    'pageType' => $item['pageType'],
                    'handle' => $item['handle'],
                    'level' => $item['level'],
                ],
                'handle' => $item['handle'],
                'pageType' => $pageTypes[$item['pageType']],
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
            $sql = "SELECT p.*
                    FROM cms_page p
                    WHERE p.sitemapId IN (SELECT node.id FROM cms_sitemap node {$where})";

            $rm = new ResultSetMapping();
            $rm->addEntityResult(Page::class, 'p');
            $rm->addFieldResult('p', 'id', 'id');
            $rm->addFieldResult('p', 'sitemapId', 'sitemapId');
            $rm->addFieldResult('p', 'locale', 'locale');
            $rm->addFieldResult('p', 'name', 'name');
            $rm->addFieldResult('p', 'slug', 'slug');
            $rm->addFieldResult('p', 'publishedFrom', 'publishedFrom');
            $rm->addFieldResult('p', 'publishedUntil', 'publishedUntil');
            $rm->addFieldResult('p', 'status', 'status');
            $rm->addFieldResult('p', 'inheritPublishedFrom', 'inheritPublishedFrom');
            $rm->addFieldResult('p', 'inheritPublishedUntil', 'inheritPublishedUntil');
            $rm->addFieldResult('p', 'inheritStatus', 'inheritStatus');
            $rm->addFieldResult('p', 'createdAt', 'createdAt');
            $rm->addFieldResult('p', 'updatedAt', 'updatedAt');
            $rm->addFieldResult('p', 'releasedAt', 'releasedAt');

            $query = $this->entityManager->createNativeQuery($sql, $rm);
            $result = $query->toIterable([]);

            foreach ($result as $page) {
                $url = '';
                try {
                    $url = $this->pageRoute->fromPageId((string)$page->id());
                } catch (\Exception $exception) {
                }

                //$flat[(string)$page->sitemapId()]['pages'][$page->locale()] = (string)$page->id();
                $flat[(string)$page->sitemapId()]['pages'][$page->locale()] = [
                    'page' => $page->toArray(),
                    'url' => $url,
                    'isOnline' => $page->isOnline(),
                ];
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
            $result = $query->toIterable([], Query::HYDRATE_OBJECT);

            foreach ($result as $item) {
                if (empty($item)) {
                    continue;
                }

                // prevent terminal pages to create an entry
                if (\array_key_exists($item['id'], $flat)) {
                    $flat[$item['id']]['navigation'][$item['pageId']][] = $item['navigation'];
                }
            }

            unset($result);
        }

        $this->store = $flat;

        $tree = [];
        foreach ($root as $item) {
            $tree[] = $this->buildTree($tree, $item);
        }
        $this->store2 = $tree;
    }

    private function buildTree(&$tree, $id): array
    {
        $child = $this->store[$id];
        if (!empty($child['children'])) {
            $children = [];
            foreach ($child['children'] as $childId) {
                $children[] = $this->buildTree($tree, $childId);
            }
            $child['children'] = $children;
        }

        return $child;
    }
}
