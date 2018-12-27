<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Metadata\PageMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
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

    public function loadMetadata(ClassMetadataBuilder $builder): void
    {
        $metadata = (new PageMetadata($builder));
    }

    public function fetchTree(): array
    {
        $queryBuilder = $this->createSelectQueryBuilder('p');
        $queryBuilder->join(Sitemap::class, 's', Join::WITH, 's.id = p.sitemapId');
        $queryBuilder->addSelect("s");
        $queryBuilder->orderBy('s.nestedLeft', 'ASC');

        $flat = $this->getFlatResult($queryBuilder->getQuery()->getResult());

        $tree = [];

        foreach ($flat as &$item) {
            if ($item['sitemap']->parentId() !== null) {
                $parent =& $flat[(string) $item['sitemap']->parentId()];
                $parent['children'][] =& $item;
                continue;
            }

            $tree[] =& $item;
        }

        return $tree;
    }

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

    private function getFlatResult(array $queryResult): array
    {
        $flat = [];
        $sitemaps = [];

        foreach ($queryResult as $item) {
            if (!($item instanceof Sitemap)) {
                continue;
            }

            $sitemaps[(string) $item->id()] = $item;
        }

        foreach ($queryResult as $item) {
            if ($item instanceof Sitemap) {
                continue;
            }

            $page = $item;
            $sitemap = $sitemaps[(string) $item->sitemapId()];

            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

            if (!empty($flat[(string)$sitemap->id()])) {
                $flat[(string)$sitemap->id()]['pages'][$page->locale()] = $page;
                continue;
            }

            $flat[(string)$sitemap->id()] = [
                'pages' => [
                    $page->locale() => $page,
                ],
                'sitemap' => $sitemap,
                'pageType' => [
                    "name" => $pageType::serviceName(),
                    "handle" => $pageType->handle(),
                    "label" => $pageType->label(),
                    "allowedChildren" => $pageType->allowedChildren(),
                    "isRoot" => $pageType->isRoot(),
                ],
                'children' => [],
            ];
        }

        return $flat;
    }
}
