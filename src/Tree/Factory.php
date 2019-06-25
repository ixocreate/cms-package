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

    public function __construct(PageTypeSubManager $pageTypeSubManager)
    {
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function createContainer(Structure $structure, array $filter = []): ContainerInterface
    {
        return new Container($structure, $this, $filter);
    }

    public function createItem(StructureItem $structureItem, array $filter = []): ItemInterface
    {
        $hash = $structureItem->structureKey();

        //TODO Hash

        if (!\array_key_exists($hash, $this->itemCache)) {
            $this->itemCache[$hash] = new Item($structureItem, $this, $this->pageTypeSubManager, $filter);
        }

        return $this->itemCache[$hash];
    }
}
