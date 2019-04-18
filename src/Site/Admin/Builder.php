<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Site\Admin;

use Ixocreate\Package\Cms\Loader\DatabasePageLoader;
use Ixocreate\Package\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Package\Cms\PageType\PageTypeSubManager;
use Ixocreate\Package\Cms\Router\PageRoute;
use Ixocreate\Package\Cms\Site\Structure\StructureBuilder;

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
