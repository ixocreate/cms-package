<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Template;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Template\Extension\ExtensionInterface;

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
     * @param PageRoute         $pageRoute
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository    $pageRepository
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
     * @param Page        $page
     * @param array       $params
     * @param null|string $locale
     * @return string
     */
    public function fromPage(Page $page, array $params = [], ?string $locale = null): string
    {
        return $this->pageRoute->fromPage($page, $params, $locale);
    }

    /**
     * @param string      $handle
     * @param array       $params
     * @param null|string $locale
     * @return string
     */
    public function fromHandle(string $handle, array $params = [], ?string $locale = null): string
    {
        $sitemap = $this->sitemapRepository->findOneBy(['handle' => $handle]);
        if (!$sitemap) {
            return '';
        }
        $page = $this->pageRepository->findOneBy([
            'sitemapId' => $sitemap->id(),
            'locale'    => $locale ?? \Locale::getDefault(),
        ]);
        if (empty($page)) {
            return '';
        }

        if (!$page->isOnline()) {
            return '';
        }

        return $this->fromPage($page, $params, $locale);
    }

    /**
     * @param Sitemap $sitemap
     * @param string $locale
     * @param string $defaultHandle
     * @return string
     */
    public function switchLanguage(Sitemap $sitemap, string $locale, string $defaultHandle): string
    {
        $page = $this->pageRepository->findOneBy([
            'sitemapId' => $sitemap->id(),
            'locale'    => $locale,
        ]);

        if (empty($page)) {
            return $this->fromHandle($defaultHandle, [], $locale);
        }

        if (!$page->isOnline()) {
            return $this->fromHandle($defaultHandle, [], $locale);
        }

        return $this->fromPage($page, [], $locale);
    }
}
