<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms\Tree;

use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Tree\AbstractContainer;
use Ixocreate\Cms\Tree\Container;
use Ixocreate\Cms\Tree\ContainerInterface;
use Ixocreate\Cms\Tree\FactoryInterface;
use Ixocreate\Cms\Tree\Item;
use Ixocreate\Cms\Tree\ItemInterface;
use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Cms\Tree\Structure\StructureStore;
use Ixocreate\Collection\CollectionInterface;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\UuidType;
use Ixocreate\Test\Schema\TypeMockHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ixocreate\Cms\Tree\AbstractContainer
 * @runTestsInSeparateProcesses
 */
class AbstractContainerTest extends TestCase
{
    /**
     * @var AbstractContainer
     */
    private $container;

    public function setUp()
    {
        (new TypeMockHelper(
            $this,
            [
                UuidType::serviceName() => new UuidType(),
                UuidType::class => new UuidType(),
                DateTimeType::serviceName() => new DateTimeType(),
                DateTimeType::class => new DateTimeType(),
            ]
        ))->create();

        $pageTypeSubManager = $this->createMock(PageTypeSubManager::class);
        $pageTypeSubManager->method('get')->willReturn($this->createMock(PageTypeInterface::class));

        $factory = new class($pageTypeSubManager) implements FactoryInterface {
            /**
             * @var PageTypeSubManager
             */
            private $pageTypeSubManager;

            public function __construct(PageTypeSubManager $pageTypeSubManager)
            {
                $this->pageTypeSubManager = $pageTypeSubManager;
            }

            public function createContainer(Structure $structure): ContainerInterface
            {
                return new Container($structure, $this);
            }

            public function createItem(StructureItem $structureItem): ItemInterface
            {
                return new Item($structureItem, $this, $this->pageTypeSubManager);
            }
        };

        $this->container = $factory->createContainer((new StructureStore(include 'tree.php'))->structure());
    }

    public function tearDown()
    {
        $this->container = null;
    }

    public function testCount()
    {
        $this->assertCount(1, $this->container);
    }

    public function testFind()
    {
        $item = $this->container->find(function (ItemInterface $item) {
            return $item->structureItem()->sitemapId() === '0fd1f906-ed75-5f8e-86e2-fe0f448ae7af';
        });

        $this->assertSame('0fd1f906-ed75-5f8e-86e2-fe0f448ae7af', (string) $item->sitemap()->id());

        $item = $this->container->find(function () {
            return false;
        });
        $this->assertNull($item);
    }

    public function testWhere()
    {
        //check when search fails
        $container = $this->container->where(function () {
            return false;
        });
        $this->assertCount(0, $container);

        $container = $this->container->where(function (ItemInterface $item) {
            if (!$item->hasPage('de_AT')) {
                return false;
            }
            return $item->page('de_AT')->status() === 'offline';
        });

        $this->assertCount(1, $container);
        $this->assertSame('c5838100-6f99-59b7-9bfa-7b3b3dc5f294', $container->current()->structureItem()->sitemapId());

        $container = $this->container->where(function (ItemInterface $item) {
            return $item->level() === 0;
        });
        $this->assertCount(1, $container);
        $this->assertSame('335eb158-98a1-57ee-9459-1403f9e8c002', $container->current()->structureItem()->sitemapId());
    }

    public function testIterator()
    {
        foreach ($this->container as $key => $value) {
            $this->assertInstanceOf(ItemInterface::class, $value);
            $this->assertIsNumeric($key);
        }
    }

    public function testFlatten()
    {
        $collection = $this->container->flatten();
        $this->assertInstanceOf(CollectionInterface::class, $collection);
        $this->assertSame(10059, $collection->count());

        $item = $collection->first();
        $this->assertInstanceOf(ItemInterface::class, $item);
    }

    public function testSearch()
    {
        $collection = $this->container->search(function (ItemInterface $item) {
            return \in_array((string) $item->sitemap()->id(), ['335eb158-98a1-57ee-9459-1403f9e8c002', '0806ee12-b2ea-5d21-9ab1-1eb220957c50']);
        });

        $this->assertInstanceOf(CollectionInterface::class, $collection);
        $this->assertSame(2, $collection->count());

        foreach ($collection as $item) {
            $this->assertInstanceOf(ItemInterface::class, $item);
            $this->assertTrue(\in_array((string) $item->sitemap()->id(), ['335eb158-98a1-57ee-9459-1403f9e8c002', '0806ee12-b2ea-5d21-9ab1-1eb220957c50']));
        }
    }
}
