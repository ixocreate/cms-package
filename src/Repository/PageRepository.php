<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Ixocreate\Cms\Entity\Navigation;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Database\Repository\AbstractRepository;

final class PageRepository extends AbstractRepository
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(EntityManagerInterface $master, PageTypeSubManager $pageTypeSubManager, SitemapRepository $sitemapRepository)
    {
        parent::__construct($master);
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Page::class;
    }

    /**
     * @return array
     */
    public function fetchNavigationTree(string $navigation, int $minLevel, int $maxLevel, string $handle, string $locale): array
    {
        $params = ['navigation' => $navigation, 'minLevel' => $minLevel, 'maxLevel' => $maxLevel, 'locale' => $locale];

        if ($handle !== null) {
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('nestedLeft', 'nestedLeft', Types::INTEGER);
            $rsm->addScalarResult('nestedRight', 'nestedRight', Types::INTEGER);
            $query = $this->getEntityManager()->createNativeQuery('SELECT nestedLeft, nestedRight FROM cms_sitemap s WHERE s.handle = :handle', $rsm);
            $result = $query->execute(['handle' => $handle], Query::HYDRATE_ARRAY);
            if (\count($result) === 0) {
                return [];
            }
            $params['nestedLeft'] = $result[0]['nestedLeft'];
            $params['nestedRight'] = $result[0]['nestedRight'];
        }

        $dql = 'SELECT p as page, s as sitemap FROM ' . Page::class . ' p
            LEFT JOIN ' . Sitemap::class . ' s WITH (p.sitemapId = s.id)
            LEFT JOIN ' . Navigation::class . ' n WITH (n.pageId = p.id)
            WHERE n.navigation = :navigation AND s.level BETWEEN :minLevel AND :maxLevel AND p.locale = :locale';
        $dql .= ' AND s.nestedLeft >= :nestedLeft AND s.nestedRight <= :nestedRight';
        $dql .= ' ORDER BY s.nestedLeft ASC';
        $result = $this->createQuery($dql)->execute($params);

//        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
//        $rsm->addRootEntityFromClassMetadata(Page::class, 'p');
//        $rsm->addEntityResult(Sitemap::class, 's', 's');
//        $rsm->addFieldResult('s', 's.id', 'id');
//        \var_dump($rsm->generateSelectClause());die();
//
//        $sql = 'SELECT ' . $rsm->generateSelectClause(['']) . ' FROM cms_page p
//            LEFT JOIN cms_sitemap s ON (p.sitemapId = s.id)
//            LEFT JOIN cms_natvigation n ON (n.pageId = p.id)
//            WHERE n.navigation = :navigation AND s.level BETWEEN :minLevel AND :maxLevel AND p.locale = :locale AND
//            s.nestedLeft >= (SELECT sx.nestedLeft FROM cms_sitemap sx WHERE sx.handle = :handle) AND s.nestedRight <= (SELECT sx.nestedRight FROM cms_sitemap sx WHERE sx.handle = :handle)
//            ORDER BY s.nestedLeft ASC';
//        $result = $this->getEntityManager()->createNativeQuery($sql, $rsm)->execute(['navigation' => $navigation, 'minLevel' => $minLevel, 'maxLevel' => $maxLevel, 'locale' => $locale, 'handle' => $handle]);

        $flat = $this->getFlatResult($result);

        $tree = [];

        foreach ($flat as &$item) {
            if ($item['sitemap']->parentId() !== null) {
                if (\array_key_exists((string)$item['sitemap']->parentId(), $flat)) {
                    $parent =& $flat[(string)$item['sitemap']->parentId()];
                    $parent['children'][] =& $item;
                    continue;
                }
            }

            $tree[] =& $item;
        }

        return $tree;
    }

    /**
     * @param Sitemap $sitemap
     * @return array
     */
    public function fetchDirectSiblingsOf(Sitemap $sitemap): array
    {
        $sitemapIds = [];
        $sitemapResult = $this->sitemapRepository->findBy(['parentId' => $sitemap->id()]);
        foreach ($sitemapResult as $item) {
            $sitemapIds[] = (string) $item->id();
        }

        if (empty($sitemapIds)) {
            return [];
        }

        $queryBuilder = $this->createSelectQueryBuilder('p');
        $queryBuilder->join(Sitemap::class, 's', Join::WITH, 's.id = p.sitemapId');
        $queryBuilder->addSelect("s");

        $or = $queryBuilder->expr()->orX();
        $or->add($queryBuilder->expr()->in('p.sitemapId', $sitemapIds));

        $queryBuilder->where($or);
        $queryBuilder->orderBy('s.nestedLeft', 'ASC');

        return \array_values($this->getFlatResult($queryBuilder->getQuery()->getResult()));
    }

    /**
     * @param array $queryResult
     * @return array
     */
    private function getFlatResult(array $queryResult): array
    {
        $flat = [];

        $resultCount = \count($queryResult);
        for ($i = 0; $i < $resultCount;) {
            $page = $queryResult[$i]['page'];
            $sitemap = $queryResult[$i + 1]['sitemap'];
            $i += 2;

            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

            $flat[(string)$sitemap->id()] = [
                'page' => $page,
                'sitemap' => $sitemap,
                'pageType' => [
                    'name' => $pageType::serviceName(),
                    'label' => $pageType->label(),
                    'allowedChildren' => $pageType->allowedChildren(),
                    'isRoot' => \is_subclass_of($pageType, RootPageTypeInterface::class),
                ],
                'children' => [],
            ];
        }

        return $flat;
    }

    public function slugExists(?string $sParentId, string $pId, string $pSlug, string $pLocale): bool
    {
        $query = $this->getEntityManager()->createQuery('SELECT COUNT (p.id) FROM ' . Page::class . ' p JOIN ' . Sitemap::class . ' s WITH p.sitemapId = s.id
        WHERE s.parentId = :parentId AND p.id != :id AND p.slug = :slug AND p.locale = :locale');
        $query->setParameters(array('parentId' => $sParentId, 'id' => $pId, 'slug'=> $pSlug, 'locale' => $pLocale));
        $result = $query->getResult();

        return $result[0][1] > 0;
    }
}
