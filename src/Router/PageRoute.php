<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Cms\Entity\Page;

/**
 * @deprecated use CmsRouter instead
 */
final class PageRoute
{
    /**
     * @var CmsRouter
     */
    private $cmsRouter;

    public function __construct(CmsRouter $cmsRouter)
    {
        $this->cmsRouter = $cmsRouter;
    }

    public function fromPage(Page $page, array $params = [], string $routePrefix = ''): string
    {
        return $this->cmsRouter->fromPage($page, $params, $routePrefix);
    }
}
