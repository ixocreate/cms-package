<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Symfony\Component\Routing\Route;

final class RouteSpecification
{
    public const NAME_MAIN = "*";

    public const NAME_INHERITANCE = "inheritance";

    /**
     * @var string[]
     */
    private $uris = [];

    /**
     * @var string
     */
    private $pageId;

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var bool
     */
    private $isRoot = true;

    /**
     * @var string
     */
    private $sitemapId;

    /**
     * @param string $name
     * @param bool $fallback
     * @throws \Exception
     * @return string
     */
    public function uri(string $name, bool $fallback = true): string
    {
        if (\array_key_exists($name, $this->uris)) {
            return $this->uris[$name];
        }

        if ($fallback === true) {
            $name = self::NAME_MAIN;
        }

        if (\array_key_exists($name, $this->uris)) {
            return $this->uris[$name];
        }

        throw new \Exception("Invalid ApplicationUri");
    }

    /**
     * @return array
     */
    public function uris(): array
    {
        return $this->uris;
    }

    /**
     * @param string $uri
     * @param string $name
     * @return RouteSpecification
     */
    public function addUri(string $uri, string $name = self::NAME_MAIN): RouteSpecification
    {
        $uri = \rtrim($uri, "/");
        $this->uris[$name] = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function pageId(): string
    {
        return $this->pageId;
    }

    /**
     * @param string $pageId
     * @return RouteSpecification
     */
    public function setPageId(string $pageId): RouteSpecification
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function sitemapId(): string
    {
        return $this->sitemapId;
    }

    /**
     * @param string $sitemapId
     * @return RouteSpecification
     */
    public function setSitemapId(string $sitemapId): RouteSpecification
    {
        $this->sitemapId = $sitemapId;

        return $this;
    }

    /**
     * @return array
     */
    public function middleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param array $middleware
     * @return RouteSpecification
     */
    public function setMiddleware(array $middleware): RouteSpecification
    {
        $this->middleware = $middleware;

        return $this;
    }

    public function addChild(RouteSpecification $routeSpecification): void
    {
        $routeSpecification->setRoot(false);
        $this->children[] = $routeSpecification;
    }

    public function children(): array
    {
        return $this->children;
    }

    public function setRoot(bool $root): void
    {
        $this->isRoot = $root;
    }

    public function isRoot(): bool
    {
        return $this->isRoot;
    }

    public function addToRouteCollection(\Symfony\Component\Routing\RouteCollection $routeCollection, $locale): void
    {
        foreach ($this->uris() as $name => $uri) {
            if ($name === self::NAME_INHERITANCE) {
                continue;
            }

            $routePrefix = 'page.';
            if ($name !== RouteSpecification::NAME_MAIN) {
                $routePrefix .= $name . '.';
            }

            $uriParts = \parse_url($uri);

            $routeObj = new Route(($uriParts['path']) ?? '/');
            if (!empty($uriParts['host'])) {
                $routeObj->setHost($uriParts['host']);
                $routeObj->setSchemes($uriParts['scheme']);
            }

            $routeObj->setDefault('pageId', $this->pageId());
            $routeObj->setDefault('locale', $locale);
            $routeObj->setDefault('sitemapId', $this->sitemapId());
            $routeObj->setDefault('middleware', $this->middleware());

            $routeName = $routePrefix . $this->pageId();
            $routeCollection->add($routeName, $routeObj);
        }

        if (\count($this->children()) > 0) {
            \var_dump("test");
            $subRouteCollection = new \Symfony\Component\Routing\RouteCollection();
            $prefix = $this->uri(self::NAME_INHERITANCE);


            /** @var RouteSpecification $routeSpecification */
            foreach ($this->children() as $routeSpecification) {
                $routeSpecification->addToRouteCollection($subRouteCollection, $locale);
            }

            if (!empty($prefix)) {
                $subRouteCollection->addPrefix($prefix);
            }

            $routeCollection->addCollection($subRouteCollection);
        }
    }
}
