<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Template;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Tree\Container;
use Ixocreate\Cms\Site\Tree\Item;
use Ixocreate\Cms\Site\Tree\Search\ActiveSearch;
use Ixocreate\Cms\Site\Tree\Search\OnlineSearch;
use Ixocreate\Template\Extension\ExtensionInterface;

final class PageUrlExtension implements ExtensionInterface
{
    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * @var Container
     */
    private $container;

    /**
     * PageUrlExtension constructor.
     *
     * @param PageRoute $pageRoute
     * @param Container $container
     */
    public function __construct(
        PageRoute $pageRoute,
        Container $container
    ) {
        $this->pageRoute = $pageRoute;
        $this->container = $container;
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
     * @param Page $page
     * @param array $params
     * @param string $routePrefix
     * @return string
     */
    public function fromPage(Page $page, array $params = [], string $routePrefix = ''): string
    {
        return $this->pageRoute->fromPage($page, $params, $routePrefix);
    }

    /**
     * @param string $handle
     * @param array $params
     * @param null|string $locale
     * @throws \Psr\Cache\InvalidArgumentException
     * @return string
     */
    public function fromHandle(string $handle, array $params = [], ?string $locale = null): string
    {
        $item = $this->container->findByHandle($handle);
        if (empty($item)) {
            return '';
        }
        $locale = $locale ?? \Locale::getDefault();
        $sitemap = $item->sitemap();

        if (!\array_key_exists($locale, $item->structureItem()->pages())) {
            return '';
        }


        $page = $item->page($locale);

        if (!$page->isOnline()) {
            return '';
        }

        $container = $this->container
            ->filter(OnlineSearch::class, ['locale' => $locale])
            ->filter(ActiveSearch::class, ['sitemap' => $sitemap])
            ->flatten();

        $item = $container->find(function (Item $item) use ($sitemap) {
            return (string) $item->sitemap()->id() === (string) $sitemap->id();
        });

        if (empty($item)) {
            return '';
        }

        return $this->fromPage($page, $params);
    }

    /**
     * @param Sitemap $sitemap
     * @param string $locale
     * @param string $defaultHandle
     * @throws \Psr\Cache\InvalidArgumentException
     * @return string
     */
    public function switchLanguage(Sitemap $sitemap, string $locale, string $defaultHandle): string
    {
        $item = $this->container->find(function (Item $item) use ($sitemap) {
            return (string) $item->sitemap()->id() === (string) $sitemap->id();
        });

        if (empty($item)) {
            return $this->fromHandle($defaultHandle, [], $locale);
        }

        if (!\array_key_exists($locale, $item->structureItem()->pages())) {
            return $this->fromHandle($defaultHandle, [], $locale);
        }

        $page = $item->page($locale);

        if (!$page->isOnline()) {
            return $this->fromHandle($defaultHandle, [], $locale);
        }

        $container = $this->container
            ->filter(OnlineSearch::class, ['locale' => $locale])
            ->filter(ActiveSearch::class, ['sitemap' => $sitemap])
            ->flatten();

        $item = $container->find(function (Item $item) use ($sitemap) {
            return (string) $item->sitemap()->id() === (string) $sitemap->id();
        });

        if (empty($item)) {
            return $this->fromHandle($defaultHandle, [], $locale);
        }

        return $this->fromPage($page, []);
    }
}
