<?php

namespace KiwiSuite\Cms\Site\Admin;


use KiwiSuite\Cms\Loader\DatabasePageLoader;
use KiwiSuite\Cms\Loader\DatabaseSitemapLoader;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Router\PageRoute;
use KiwiSuite\Cms\Site\Structure\StructureBuilder;

class Builder
{
    /**
     * @var StructureBuilder
     */
    private $structureBuilder;
    /**
     * @var DatabasePageLoader
     */
    private $databasePageLoader;
    /**
     * @var DatabaseSitemapLoader
     */
    private $databaseSitemapLoader;
    /**
     * @var PageRoute
     */
    private $pageRoute;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    public function __construct(
        StructureBuilder $structureBuilder,
        DatabasePageLoader $databasePageLoader,
        DatabaseSitemapLoader $databaseSitemapLoader,
        PageRoute $pageRoute,
        PageTypeSubManager $pageTypeSubManager
    ) {
        $this->structureBuilder = $structureBuilder;
        $this->databasePageLoader = $databasePageLoader;
        $this->databaseSitemapLoader = $databaseSitemapLoader;
        $this->pageRoute = $pageRoute;
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function build(): Container
    {
        return new Container(
            $this->databaseSitemapLoader,
            $this->databasePageLoader,
            $this->pageTypeSubManager,
            $this->pageRoute,
            $this->structureBuilder->build()->structure()
        );
    }
}
