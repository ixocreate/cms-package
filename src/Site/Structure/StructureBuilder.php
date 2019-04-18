<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Structure;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

final class StructureBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $master)
    {
        $this->entityManager = $master;
    }

    public function build(): Structure
    {
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
        foreach ($flat as &$item) {
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
