<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\ProjectUri\ProjectUri;

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
     * @var ProjectUri
     */
    private $projectUri;

    public function __construct(Config $config, CmsRouter $cmsRouter, ProjectUri $projectUri)
    {
        $this->config = $config;
        $this->cmsRouter = $cmsRouter;
        $this->projectUri = $projectUri;
    }

    public function fromPage(Page $page, array $params = [], ?string $locale = null): string
    {
        return $this->cmsRouter->generateUri("page." . (string)$page->id(), $params, ['locale' => $locale ?? $page->locale()]);
    }
}
