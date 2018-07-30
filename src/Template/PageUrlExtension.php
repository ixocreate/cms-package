<?php

namespace KiwiSuite\Cms\Template;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Cms\Router\PageRoute;
use KiwiSuite\Contract\Template\ExtensionInterface;

final class PageUrlExtension implements ExtensionInterface
{
    /**
     * @var PageRoute
     */
    private $pageRoute;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * PageUrlExtension constructor.
     *
     * @param PageRoute $pageRoute
     */
    public function __construct(
        PageRoute $pageRoute,
        SitemapRepository $sitemapRepository,
        PageRepository $pageRepository
    ) {
        $this->pageRoute = $pageRoute;
        $this->sitemapRepository = $sitemapRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'pageUrl';
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param Page  $page
     * @param array $params
     * @return string
     */
    public function fromPage(Page $page, array $params = [], ?string $locale = null): string
    {
        return $this->pageRoute->fromPage($page, $params, $locale);
    }

    /**
     * @param Page  $page
     * @param array $params
     * @return string
     */
    public function fromHandle(string $handle, array $params = [], ?string $locale = null): string
    {
        $sitemap = $this->sitemapRepository->findOneBy(['handle' => $handle]);
        if (!$sitemap) {
            return null;
        }
        $page = $this->pageRepository->findOneBy([
            'sitemapId' => $sitemap->id(),
            'locale'    => $locale ? $locale : \Locale::getDefault(),
        ]);
        if (!$page) {
            return null;
        }
        return $this->fromPage($page, $params, $locale);
    }
}
