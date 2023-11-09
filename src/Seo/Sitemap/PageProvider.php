<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Seo\Sitemap;

use Doctrine\Common\Collections\Criteria;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Router\PageRoute;

class PageProvider implements XmlSitemapProviderInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    public function __construct(PageRepository $pageRepository, PageVersionRepository $pageVersionRepository, PageRoute $pageRoute)
    {
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRoute = $pageRoute;
    }

    public static function serviceName(): string
    {
        return 'pages';
    }

    public function writeUrls(UrlsetCollector $urlset)
    {
        $this->fetchUrls($urlset);
    }

    public function writePingUrls(UrlsetCollector $urlset, \DateTime $fromDate)
    {
        $this->fetchUrls($urlset, $fromDate);
    }

    private function fetchUrls(UrlsetCollector $urlset, $from = null)
    {
        $criteria = new Criteria();
        $criteria->andWhere(Criteria::expr()->eq('status', 'online'))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->isNull('publishedFrom'),
                Criteria::expr()->lte('publishedFrom', new \DateTime())
            ))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->isNull('publishedUntil'),
                Criteria::expr()->gte('publishedUntil', new \DateTime())
            ));

        if ($from !== null) {
            $criteria->andWhere(Criteria::expr()->gte('updatedAt', $from));
        }

        $criteria->orderBy(['createdAt' => 'ASC']);

        $pages = $this->pageRepository->matching($criteria);

        foreach ($pages as $page) {
            /** @var Page $page */
            $loc = $this->pageRoute->fromPage($page);

            $versionCriteria = new Criteria();
            $versionCriteria->where(Criteria::expr()->eq('pageId', $page->id()));
            $versionCriteria->setMaxResults(1);
            $versionCriteria->orderBy(['createdAt' => 'DESC']);
            /** @var PageVersion $version */
            $version = $this->pageVersionRepository->matching($versionCriteria)->first();

            if($version) {
                $lastMod = $version->createdAt()->value();
            } else {
                $lastMod = $page->updatedAt()->value();
            }

            $url = new Url($loc, $lastMod);

            $urlset->add($url);
        }
    }
}
