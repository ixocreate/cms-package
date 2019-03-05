<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Site\Tree;

use Ixocreate\Cms\Site\Structure\StructureItem;

final class ItemFactory
{

    public function create(StructureItem $structureItem): Item
    {
        return new Item($structureItem, $this);
    }
}
