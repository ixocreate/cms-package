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
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Cms\Tree\Container;
use Ixocreate\Cms\Tree\Item;
use Ixocreate\Template\Extension\ExtensionInterface;

final class PageUrlExtension implements ExtensionInterface
{
    /**
     * @var Container
*/
    private $container;

    /**
     * @var CmsRouter
     */
    private $cmsRouter;

    /**
     * PageUrlExtension constructor.
     *
     * @param CmsRouter $cmsRouter
     * @param Container $container
     */
    public function __construct(
        CmsRouter $cmsRouter,
        Container $container
    ) {
        $this->container = $container;
        $this->cmsRouter = $cmsRouter;
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
     * @throws \Exception
     * @return string
     */
    public function fromPage(Page $page, array $params = [], string $routePrefix = ''): string
    {
        try {
            return $this->cmsRouter->fromPage($page, $params, $routePrefix);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param string $handle
     * @param array $params
     * @param null|string $locale
     * @param string $routePrefix
     * @throws \Exception
     * @return string
     */
    public function fromHandle(string $handle, array $params = [], ?string $locale = null, string $routePrefix = ''): string
    {
        $item = $this->container->find(function (Item $item) use ($handle) {
            return $item->handle() === $handle;
        });
        if (empty($item)) {
            return '';
        }
        $locale = $locale ?? \Locale::getDefault();

        if (!$item->hasPage($locale)) {
            return '';
        }

        if (!$item->isOnline($locale)) {
            return '';
        }

        return $this->fromPage($item->page($locale), $params, $routePrefix);
    }

    /**
     * @param Sitemap $sitemap
     * @param string $locale
     * @param string $defaultHandle
     * @param string $routePrefix
     * @throws \Exception
     * @return string
     */
    public function switchLanguage(Sitemap $sitemap, string $locale, string $defaultHandle, string $routePrefix = ''): string
    {
        $item = $this->container->find(function (Item $item) use ($sitemap) {
            return (string) $item->structureItem()->sitemapId() === (string) $sitemap->id();
        });

        if (empty($item)) {
            return $this->fromHandle($defaultHandle, [], $locale, $routePrefix);
        }

        if (!$item->hasPage($locale)) {
            return $this->fromHandle($defaultHandle, [], $locale, $routePrefix);
        }

        if (!$item->isOnline($locale)) {
            return $this->fromHandle($defaultHandle, [], $locale, $routePrefix);
        }

        return $this->fromPage($item->page($locale), [], $routePrefix);
    }
}
