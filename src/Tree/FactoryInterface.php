<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureItem;

interface FactoryInterface
{
    public function createContainer(Structure $structure): ContainerInterface;

    public function createItem(StructureItem $structureItem): ItemInterface;
}
