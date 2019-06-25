<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Router\Tree;

use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Tree\ContainerInterface;
use Ixocreate\Cms\Tree\FactoryInterface;
use Ixocreate\Cms\Tree\ItemInterface;
use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureItem;

final class Factory implements FactoryInterface
{
    /**
     * @var array
     */
    private $itemCache = [];

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var ReplacementManager
     */
    private $replacementManager;

    public function __construct(PageTypeSubManager $pageTypeSubManager, ReplacementManager $replacementManager)
    {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->replacementManager = $replacementManager;
    }

    public function createContainer(Structure $structure, array $filter = []): ContainerInterface
    {
        return new RoutingContainer($structure, $this, $filter);
    }

    public function createItem(StructureItem $structureItem, array $filter = []): ItemInterface
    {
        $hash = $structureItem->structureKey();

        //TODO Hash

        if (!\array_key_exists($hash, $this->itemCache)) {
            $this->itemCache[$hash] = new RoutingItem(
                $structureItem,
                $this,
                $this->pageTypeSubManager,
                $this->replacementManager,
                $filter
            );
        }

        return $this->itemCache[$hash];
    }
}
