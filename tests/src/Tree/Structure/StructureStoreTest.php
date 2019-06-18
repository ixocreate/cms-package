<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms\Tree\Structure;

use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureStore;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class StructureStoreTest extends TestCase
{
    private $structureStore;

    public function setUp()
    {
        $this->structureStore = new StructureStore(include 'tree.php');
    }

    public function tearDown()
    {
        $this->structureStore = null;
    }

    public function testStructure()
    {
        $this->assertInstanceOf(Structure::class, $this->structureStore->structure());
        $this->assertSame(0, $this->structureStore->structure()->level());
    }

    public function testItem()
    {
        $structureStore = $this->structureStore->item('0.0');

        $this->assertSame('6674fe9a-867f-5fc5-b0aa-4793718c1528', $structureStore['sitemap']['id']);
        $this->assertSame([
            '0.0.0',
            '0.0.1',
            '0.0.2',
            '0.0.3',
            '0.0.4',
            '0.0.5',
            '0.0.6',
            '0.0.7',
            '0.0.8',
            '0.0.9',
        ], $structureStore['children']);
    }
}
