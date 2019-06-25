<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Tree;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Action\Frontend\RenderAction;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Middleware\LoadPageContentMiddleware;
use Ixocreate\Cms\Middleware\LoadPageMiddleware;
use Ixocreate\Cms\Middleware\LoadPageTypeMiddleware;
use Ixocreate\Cms\Middleware\LoadSitemapMiddleware;
use Ixocreate\Cms\PageType\MiddlewarePageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\RoutingAwareInterface;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\Cms\Tree\AbstractItem;
use Ixocreate\Cms\Tree\FactoryInterface;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ServiceManager\SubManager\SubManagerInterface;

final class RoutingItem extends AbstractItem
{
    /**
     * @var array
     */
    private $pageRoute = [];
    /**
     * @var ReplacementManager
     */
    private $replacementManager;

    public function __construct(
        \Ixocreate\Cms\Tree\Structure\StructureItem $structureItem,
        FactoryInterface $factory,
        PageTypeSubManager $pageTypeSubManager,
        ReplacementManager $replacementManager,
        array $filter = []
    ){
        parent::__construct(
            $structureItem,
            $factory,
            $pageTypeSubManager,
            $filter
        );
        $this->replacementManager = $replacementManager;
    }

    public function pageRoute(string $locale): ?RouteSpecification
    {
        if (\array_key_exists($locale, $this->pageRoute)) {
            return $this->pageRoute[$locale];
        }
        if (!$this->hasPage($locale)) {
            $this->pageRoute[$locale] = null;
            return null;
        }
        $pageId = $this->structureItem()->pageId($locale);

        $pageType = $this->pageType();
        $routing = '${PARENT}/${SLUG}';
        if ($pageType instanceof RoutingAwareInterface) {
            $routing = $pageType->routing();
        } elseif ($pageType instanceof RootPageTypeInterface) {
            $routing = '/';
        }

        $middleware = [
            LoadPageMiddleware::class,
            LoadSitemapMiddleware::class,
            LoadPageTypeMiddleware::class,
            LoadPageContentMiddleware::class,
        ];

        if ($pageType instanceof MiddlewarePageTypeInterface) {
            $middleware = \array_merge($middleware, \array_values($pageType->middleware()));
        }
        $middleware[] = RenderAction::class;

        $routeSpecification = new RouteSpecification();
        $routeSpecification->addUri($routing);
        $routeSpecification->setPageId($pageId);
        $routeSpecification->setStructureKey($this->structureItem()->structureKey());
        $routeSpecification->setMiddleware($middleware);

        foreach ($this->replacementManager->replacementServices() as $replacement) {
            $replacement->replace($routeSpecification, $locale, $this);
        }

        $this->pageRoute[$locale] = $routeSpecification;
        return $routeSpecification;
    }
}
