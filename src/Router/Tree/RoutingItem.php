<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Tree;

use Ixocreate\Cms\Action\Frontend\RenderAction;
use Ixocreate\Cms\Middleware\LoadPageContentMiddleware;
use Ixocreate\Cms\Middleware\LoadPageMiddleware;
use Ixocreate\Cms\PageType\MiddlewarePageTypeInterface;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\RoutingAwareInterface;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Strategy\LoaderInterface;
use Ixocreate\Cms\Tree\AbstractItem;
use Ixocreate\Cms\Tree\MutationCollection;
use Ixocreate\Cms\Tree\TreeFactoryInterface;

final class RoutingItem extends AbstractItem
{
    /**
     * @var array
     */
    private static $pageRoute = [];

    /**
     * @var ReplacementManager
     */
    private $replacementManager;

    public function __construct(
        string $id,
        MutationCollection $mutationCollection,
        TreeFactoryInterface $treeFactory,
        LoaderInterface $loader,
        ReplacementManager $replacementManager
    ) {
        parent::__construct($id, $mutationCollection, $treeFactory, $loader);
        $this->replacementManager = $replacementManager;
    }

    public function pageRoute(string $locale): ?RouteSpecification
    {
        if (!$this->hasPage($locale)) {
            return null;
        }

        $pageId = (string) $this->page($locale)->id();
        if (isset(self::$pageRoute[$pageId])) {
            return self::$pageRoute[$pageId];
        }

        $pageType = $this->pageType();
        $routing = '${PARENT}/${SLUG}';
        if ($pageType instanceof RoutingAwareInterface) {
            $routing = $pageType->routing();
        } elseif ($pageType instanceof RootPageTypeInterface) {
            $routing = '/';
        }

        $middleware = [
            LoadPageMiddleware::class,
            LoadPageContentMiddleware::class,
        ];

        if ($pageType instanceof MiddlewarePageTypeInterface) {
            $middleware = \array_merge($middleware, \array_values($pageType->middleware()));
        }
        $middleware[] = RenderAction::class;

        $routeSpecification = new RouteSpecification();
        $routeSpecification->addUri($routing);
        $routeSpecification->setPageId($pageId);
        $routeSpecification->setSitemapId($this->id());
        $routeSpecification->setMiddleware($middleware);

        foreach ($this->replacementManager->replacementServices() as $replacement) {
            $replacement->replace($routeSpecification, $locale, $this);
        }

        self::$pageRoute[$pageId] = $routeSpecification;
        return $routeSpecification;
    }
}
