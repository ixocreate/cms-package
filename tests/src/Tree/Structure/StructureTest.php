<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms\Tree\Structure;

use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Cms\Tree\Structure\StructureStore;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ixocreate\Cms\Tree\Structure\Structure
 * @runTestsInSeparateProcesses
 */
class StructureTest extends TestCase
{
    public function testStructure()
    {
        $structure = (new StructureStore(include 'tree.php'))->structure();

        $this->assertSame(1, $structure->count());
        $this->assertSame(0, $structure->level());
        $this->assertNull($structure->parent());

        $i = 0;
        foreach ($structure as $key => $item) {
            $this->assertInstanceOf(StructureItem::class, $item);
            $this->assertSame(0, $item->level());
            $this->assertSame($i, $key);
            $i++;
        }
        $structure->rewind();

        $structure = $structure->current()->structure();
        $this->assertSame(3, $structure->count());
        $this->assertSame(1, $structure->level());
        $this->assertInstanceOf(StructureItem::class, $structure->parent());

        $newStructure = $structure->only(function (StructureItem $structureItem) {
            return false;
        });
        $this->assertSame(0, $newStructure->count());

        $newStructure = $structure->only(function (StructureItem $structureItem) {
            return $structureItem->sitemapId() === '6674fe9a-867f-5fc5-b0aa-4793718c1528';
        });
        $this->assertSame(1, $newStructure->count());
    }
}
