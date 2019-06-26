<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Admin;

use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Cms\Tree\ContainerInterface;
use Ixocreate\Cms\Tree\FactoryInterface;
use Ixocreate\Cms\Tree\FilterManager;
use Ixocreate\Cms\Tree\ItemInterface;
use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Intl\LocaleManager;

final class Factory implements FactoryInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var FilterManager
     */
    private $filterManager;
    /**
     * @var LocaleManager
     */
    private $localeManager;
    /**
     * @var CmsRouter
     */
    private $cmsRouter;

    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        FilterManager $filterManager,
        LocaleManager $localeManager,
        CmsRouter $cmsRouter
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->filterManager = $filterManager;
        $this->localeManager = $localeManager;
        $this->cmsRouter = $cmsRouter;
    }

    public function createContainer(Structure $structure, array $filter = []): ContainerInterface
    {
        return new Container($structure, $this, $this->filterManager, $filter);
    }

    public function createItem(StructureItem $structureItem, array $filter = []): ItemInterface
    {
        return new Item(
            $structureItem,
            $this,
            $this->pageTypeSubManager,
            $this->filterManager,
            $this->localeManager,
            $this->cmsRouter,
            $filter
        );
    }
}
