<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\PageType\PageTypeSubManager;
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
     * @var FilterManager
     */
    private $filterManager;

    public function __construct(PageTypeSubManager $pageTypeSubManager, FilterManager $filterManager)
    {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->filterManager = $filterManager;
    }

    public function createContainer(Structure $structure, array $filter = []): ContainerInterface
    {
        return new Container($structure, $this, $this->filterManager, $filter);
    }

    public function createItem(StructureItem $structureItem, array $filter = []): ItemInterface
    {
        $hash = $structureItem->structureKey();

        //TODO Hash

        if (!\array_key_exists($hash, $this->itemCache)) {
            $this->itemCache[$hash] = new Item($structureItem, $this, $this->pageTypeSubManager, $this->filterManager, $filter);
        }

        return $this->itemCache[$hash];
    }
}
