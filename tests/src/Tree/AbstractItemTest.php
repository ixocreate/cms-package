<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms\Tree;

use Exception;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Tree\AbstractItem;
use Ixocreate\Cms\Tree\Container;
use Ixocreate\Cms\Tree\ContainerInterface;
use Ixocreate\Cms\Tree\FactoryInterface;
use Ixocreate\Cms\Tree\Item;
use Ixocreate\Cms\Tree\ItemInterface;
use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Cms\Tree\Structure\StructureStore;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\UuidType;
use Ixocreate\Test\Schema\TypeMockHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ixocreate\Cms\Tree\AbstractItem
 * @runTestsInSeparateProcesses
 */
class AbstractItemTest extends TestCase
{
    /**
     * @var AbstractItem
     */
    private $item;

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

            public function createContainer(Structure $structure, array $filter = []): ContainerInterface
            {
                return new Container($structure, $this, $filter);
            }

            public function createItem(StructureItem $structureItem, array $filter = []): ItemInterface
            {
                return new Item($structureItem, $this, $this->pageTypeSubManager, $filter);
            }
        };

        $this->item = $factory->createItem((new StructureStore(include 'tree.php'))->structure()->current());
    }

    public function tearDown()
    {
        $this->item = null;
    }

    public function testHasChildren()
    {
        $this->assertTrue($this->item->hasChildren());
    }

    public function testCount()
    {
        $this->assertCount(3, $this->item);
        $this->assertSame(3, $this->item->count());
    }

    public function testSitemap()
    {
        $sitemap = $this->item->sitemap();
        $this->assertInstanceOf(Sitemap::class, $sitemap);
        $this->assertSame('335eb158-98a1-57ee-9459-1403f9e8c002', (string) $sitemap->id());
    }

    public function testHasPage()
    {
        $this->assertTrue($this->item->hasPage('de_AT'));
        $this->assertFalse($this->item->hasPage('en_GB'));

        \Locale::setDefault('en_US');
        $this->assertTrue($this->item->hasPage());
        \Locale::setDefault('en_GB');
        $this->assertFalse($this->item->hasPage());
    }

    public function testPage()
    {
        $page = $this->item->page('de_AT');

        $this->assertSame('deb7b17e-db05-5e0b-8cb5-8a8d0ec36808', (string) $page->id());
    }

    public function testPageException()
    {
        $this->expectException(Exception::class);
        $this->item->page('en_GB');
    }

    public function testIsOnline()
    {
        $this->assertFalse($this->item->isOnline('en_GB'));
        $this->assertFalse($this->item->isOnline('en_US'));

        $this->assertFalse($this->item->below()->current()->isOnline('en_US'));

        $this->assertTrue($this->item->isOnline('de_AT'));
    }

    public function testPageType()
    {
        $this->assertInstanceOf(PageTypeInterface::class, $this->item->pageType());
    }

    public function testHandle()
    {
        $this->assertSame('home', $this->item->handle());
    }

    public function testNavigation()
    {
        $this->assertSame([], $this->item->navigation('de_AT'));
        $this->assertSame([], $this->item->navigation('en_GB'));

        $this->assertSame(['main'], $this->item->below()->current()->navigation('de_AT'));
    }

    public function testLevel()
    {
        $this->assertSame(0, $this->item->level());
        $this->assertSame(1, $this->item->below()->current()->level());
    }

    public function testStructureItem()
    {
        $this->assertSame((string)$this->item->sitemap()->id(), $this->item->structureItem()->sitemapId());
    }

    public function testBelow()
    {
        $container = $this->item->below();
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertSame($this->item->count(), $container->count());
    }

    public function testParent()
    {
        $this->assertNull($this->item->parent());

        $this->assertSame($this->item->structureItem()->sitemapId(), $this->item->below()->current()->parent()->structureItem()->sitemapId());
    }

    public function testGetChildren()
    {
        $this->assertSame((string) $this->item->current()->sitemap()->id(), (string) $this->item->getChildren()->sitemap()->id());
    }

    public function testIteration()
    {
        foreach ($this->item as $key => $item) {
            $this->assertIsNumeric($key);
            $this->assertInstanceOf(ItemInterface::class, $item);
        }
    }
}
