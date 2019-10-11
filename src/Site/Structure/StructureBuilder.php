<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Structure;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;

final class StructureBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    public function __construct(EntityManagerInterface $master, PageTypeSubManager $pageTypeSubManager)
    {
        $this->entityManager = $master;
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function build($excludeTerminal = true): Structure
    {
        if ($excludeTerminal) {
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

                $ids = [];
                foreach ($result as $row) {
                   $ids[] = $row['id'];
                }
                $where = 'WHERE s.parentId IS NULL OR s.parentId NOT IN (\'' . \implode('\',\'', $ids) . '\')';;
            }

            $sql = "SELECT 
                    s.id, 
                    s.parentId,
                    s.handle,
                    p.id as pageId, 
                    p.locale, 
                    n.navigation 
                FROM 
                    cms_sitemap AS s 
                    LEFT JOIN cms_page AS p ON (p.sitemapId = s.id)
                    LEFT JOIN cms_navigation AS n ON (p.id = n.pageId)
                    {$where}
                ORDER BY s.nestedLeft";
        } else {
            $sql = "SELECT 
                    s.id, 
                    s.parentId,
                    s.handle,
                    p.id as pageId, 
                    p.locale, 
                    n.navigation 
                FROM 
                    cms_sitemap AS s 
                    LEFT JOIN cms_page AS p ON (p.sitemapId = s.id)
                    LEFT JOIN cms_navigation AS n ON (p.id = n.pageId)
                ORDER BY s.nestedLeft";
        }

        $rm = new ResultSetMapping();
        $rm->addScalarResult('id', 'id', 'string');
        $rm->addScalarResult('parentId', 'parentId', 'string');
        $rm->addScalarResult('handle', 'handle', 'string');
        $rm->addScalarResult('pageId', 'pageId', 'string');
        $rm->addScalarResult('locale', 'locale', 'string');
        $rm->addScalarResult('navigation', 'navigation', 'string');

        $query = $this->entityManager->createNativeQuery($sql, $rm);

        $result = $query->getResult();

        $flat = [];
        foreach ($result as $item) {
            if (!\array_key_exists($item['id'], $flat)) {
                $flat[$item['id']] = [
                    'sitemapId' => $item['id'],
                    'parentId' => $item['parentId'],
                    'handle' => $item['handle'],
                    'pages' => [],
                    'navigation' => [],
                    'children' => [],
                ];
            }

            if (empty($item['pageId'])) {
                continue;
            }

            $flat[$item['id']]['pages'][$item['locale']] = $item['pageId'];

            if (!empty($item['navigation'])) {
                $flat[$item['id']]['navigation'][] = $item['navigation'];
            }
        }
        unset($result);

        $tree = [];
        foreach ($flat as $key => &$item) {
            if ($item['parentId'] !== null) {
                $parent =& $flat[$item['parentId']];
                $parent['children'][] =& $item;
                continue;
            }

            $tree[] =& $item;
        }

        foreach ($flat as &$item) {
            unset($item['parentId']);
            $item['navigation'] = \array_unique($item['navigation']);
        }

        return new Structure($tree);
    }
}
