<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Entity\Page;

final class PageRoute
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CmsRouter
     */
    private $cmsRouter;

    /**
     * @var ApplicationUri
     */
    private $projectUri;

    public function __construct(Config $config, CmsRouter $cmsRouter, ApplicationUri $projectUri)
    {
        $this->config = $config;
        $this->cmsRouter = $cmsRouter;
        $this->projectUri = $projectUri;
    }

    public function fromPage(Page $page, array $params = [], string $routePrefix = ''): string
    {
        if ($routePrefix !== '') {
            $routePrefix .= '.';
        }
        return $this->cmsRouter->generateUri('page.' . $routePrefix . (string)$page->id(), $params);
    }
}
