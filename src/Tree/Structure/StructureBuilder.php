<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Structure;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Entity\Definition;

final class StructureBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * StructureBuilder constructor.
     * @param EntityManagerInterface $master
     */
    public function __construct(EntityManagerInterface $master)
    {
        $this->entityManager = $master;
    }

    /**
     * @return Structure
     */
    public function build(): Structure
    {
        $scalarResult = [];

        $definitions = Sitemap::getDefinitions();
        $sitemapSelect = [];
        /** @var Definition $definition */
        foreach ($definitions as $definition) {
            $sitemapSelect[] = 's.' . $definition->getName() . ' as sitemap_' . $definition->getName();
            $scalarResult[] = [
                'name' => 'sitemap_' . $definition->getName(),
                'type' => 'string',
            ];
        }
        $sitemapSelect = \implode(', ', $sitemapSelect);

        $definitions = Page::getDefinitions();
        $pageSelect = [];
        /** @var Definition $definition */
        foreach ($definitions as $definition) {
            $pageSelect[] = 'p.' . $definition->getName() . ' as page_' . $definition->getName();
            $scalarResult[] = [
                'name' => 'page_' . $definition->getName(),
                'type' => 'string',
            ];
        }
        $pageSelect = \implode(', ', $pageSelect);

        $sql = "SELECT 
                    {$sitemapSelect},
                    {$pageSelect}
                FROM 
                    cms_sitemap AS s
                    LEFT JOIN cms_page AS p ON (p.sitemapId = s.id)
                ORDER BY s.nestedLeft";

        $rm = new ResultSetMapping();
        foreach ($scalarResult as $scalar) {
            $rm->addScalarResult($scalar['name'], $scalar['name'], $scalar['type']);
        }

        $query = $this->entityManager->createNativeQuery($sql, $rm);

        $result = $query->getResult();

        $flat = [];
        foreach ($result as $item) {
            if (!\array_key_exists($item['sitemap_id'], $flat)) {
                $sitemap = $this->objectData($item, 'sitemap_');
                $flat[$item['sitemap_id']] = [
                    'sitemap' => $sitemap,
                    'pages' => [],
                    'children' => [],
                ];
            }
            $page = $this->objectData($item, 'page_');
            $flat[$item['sitemap_id']]['pages'][$item['page_locale']] = $page;
        }
        unset($result);

        $tree = [];
        foreach ($flat as &$item) {
            if ($item['sitemap']['parentId'] !== null) {
                $parent =& $flat[$item['sitemap']['parentId']];
                $parent['children'][] =& $item;
                continue;
            }

            $tree[] =& $item;
        }


        return (new StructureStore($tree))->structure();
    }

    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    private function objectData(array $array, string $prefix): array
    {
        $data = [];

        $filteredData = \array_filter($array, function ($key) use ($prefix){
            return (\substr($key, 0, \strlen($prefix)) === $prefix);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($filteredData as $key => $value) {
            $key = \str_replace($prefix, '', $key);
            $data[$key] = $value;
        }

        return $data;
    }
}
