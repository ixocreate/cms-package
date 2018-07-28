<?php
namespace KiwiSuite\Cms\Router;

use KiwiSuite\Cms\Config\Config;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\ProjectUri\ProjectUri;

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

    public function fromPage(Page $page, array $params = []): string
    {
        return $this->cmsRouter->generateUri("page." . (string) $page->id(), $params, ['locale' => $page->locale()]);
    }
}
