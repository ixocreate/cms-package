<?php

namespace KiwiSuite\Cms\Repository;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Metadata\PageMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Database\Repository\AbstractRepository;

final class PageRepository extends AbstractRepository
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    public function __construct(EntityManagerInterface $master, PageTypeSubManager $pageTypeSubManager)
    {
        parent::__construct($master);
        $this->pageTypeSubManager = $pageTypeSubManager;
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
        $result = $queryBuilder->getQuery()->getResult();

        $flat = [];
        for ($i = 0; $i < count($result); $i++) {
            /** @var Page $page */
            $page = $result[$i++];
            /** @var Sitemap $sitemap */
            $sitemap = $result[$i];

            /** @var PageTypeInterface $pageType */
            $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

            if (!empty($flat[(string)$sitemap->id()])) {
                $flat[(string)$sitemap->id()]['pages'][$page->locale()] = $page;
                continue;
            }

            $flat[(string)$sitemap->id()] = [
                'pages' => [
                    $page->locale() => $page
                ],
                'sitemap' => $sitemap,
                'pageType' => [
                    "handle" => $pageType->handle(),
                    "label" => $pageType->label(),
                    "allowedChildren" => $pageType->allowedChildren(),
                    "isRoot" => $pageType->isRoot(),
                ],
                'children' => [],
            ];
        }

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
}

