<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms\Tree\Structure;

use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Cms\Tree\Structure\StructureStore;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ixocreate\Cms\Tree\Structure\StructureItem
 * @runTestsInSeparateProcesses
 */
class StructureItemTest extends TestCase
{
    /**
     * @var StructureItem
     */
    private $structureItem;

    /**
     * @var Structure
     */
    private $structure;

    public function setUp()
    {
        $this->structure = (new StructureStore(include 'tree.php'))->structure();
        $this->structureItem = $this->structure->current();
    }

    public function tearDown()
    {
        $this->structure = null;
        $this->structureItem = null;
    }

    private function checkSitemap(StructureItem $structureItem, array $sitemapData)
    {
        $this->assertSame($sitemapData['id'], $structureItem->sitemapId());
        $this->assertIsArray($structureItem->sitemapData());

        foreach (['id', 'parentId', 'nestedLeft', 'nestedRight', 'pageType', 'handle'] as $key) {
            $this->assertArrayHasKey($key, $structureItem->sitemapData());
            $this->assertSame($sitemapData[$key], $structureItem->sitemapData()[$key]);
        }
    }

    private function checkPage(StructureItem $structureItem, array $locales, array $pageData)
    {
        foreach ($locales as $locale) {
            if (!isset($pageData[$locale]['id'])) {
                $this->assertSame('', $structureItem->pageId($locale));
                $this->assertSame([], $structureItem->pageData($locale));

                continue;
            }

            $this->assertSame($pageData[$locale]['id'], $structureItem->pageId($locale));
            $this->assertIsArray($structureItem->pageData($locale));

            foreach (['id', 'sitemapId', 'locale', 'name', 'slug', 'publishedFrom', 'publishedUntil', 'status', 'updatedAt', 'createdAt', 'releasedAt'] as $key) {
                $this->assertArrayHasKey($key, $structureItem->pageData($locale));
                $this->assertSame($pageData[$locale][$key], $structureItem->pageData($locale)[$key]);
            }
        }
    }

    public function testSitemap()
    {
        $this->checkSitemap($this->structureItem, [
            'id' => '335eb158-98a1-57ee-9459-1403f9e8c002',
            'parentId' => null,
            'nestedLeft' => '1',
            'nestedRight' => '20118',
            'pageType' => 'home',
            'handle' => 'home',
        ]);
    }

    public function testPages()
    {
        $this->checkPage($this->structureItem, ['de_AT', 'de_DE', 'en_US'], [
            'de_AT' => [
                'id' => 'deb7b17e-db05-5e0b-8cb5-8a8d0ec36808',
                'sitemapId' => '335eb158-98a1-57ee-9459-1403f9e8c002',
                'locale' => 'de_AT',
                'name' => 'Page 1',
                'slug' => 'page-1',
                'publishedFrom' => null,
                'publishedUntil' => null,
                'status' => 'online',
                'updatedAt' => '2019-06-07 13:50:34',
                'createdAt' => '2019-06-07 13:50:34',
                'releasedAt' => '2019-06-07 13:50:34',
            ],
            'de_DE' => [
                'id' => '19d0643a-cc13-553b-8fdd-6a801f99c06c',
                'sitemapId' => '335eb158-98a1-57ee-9459-1403f9e8c002',
                'locale' => 'de_DE',
                'name' => 'Page 2',
                'slug' => 'page-2',
                'publishedFrom' => null,
                'publishedUntil' => null,
                'status' => 'online',
                'updatedAt' => '2019-06-07 13:50:34',
                'createdAt' => '2019-06-07 13:50:34',
                'releasedAt' => '2019-06-07 13:50:34',
            ],
            'en_US' => [
                'id' => '3d55ea86-aafc-5765-ac6d-0c1d520f0779',
                'sitemapId' => '335eb158-98a1-57ee-9459-1403f9e8c002',
                'locale' => 'en_US',
                'name' => 'Page 3',
                'slug' => 'page-3',
                'publishedFrom' => null,
                'publishedUntil' => null,
                'status' => 'offline',
                'updatedAt' => '2019-06-07 13:50:34',
                'createdAt' => '2019-06-07 13:50:34',
                'releasedAt' => '2019-06-07 13:50:34',
            ],
        ]);

        $structure = $this->structureItem->structure();
        $structure->next();
        $structureItem = $structure->current();
        $this->checkPage($structureItem, ['de_AT', 'de_DE', 'en_US'], [
            'de_AT' => [],
            'de_DE' => [
                'id' => 'eb4904b3-8eee-5c42-953d-5db477c4ba6c',
                'sitemapId' => 'a1d5f6eb-79fe-5a05-bf1d-dbc83ace8b78',
                'locale' => 'de_DE',
                'name' => 'Page 7',
                'slug' => 'page-7',
                'publishedFrom' => null,
                'publishedUntil' => null,
                'status' => 'online',
                'updatedAt' => '2019-06-07 13:50:34',
                'createdAt' => '2019-06-07 13:50:34',
                'releasedAt' => '2019-06-07 13:50:34',
            ],
            'en_US' => [],
        ]);
    }

    public function testPageType()
    {
        $this->assertSame('home', $this->structureItem->pageType());
    }

    public function testHandle()
    {
        $this->assertSame('home', $this->structureItem->handle());

        $this->structure->rewind();
        $structureItem = $this->structure->current()->structure()->current();
        $this->assertNull($structureItem->handle());
    }

    public function testLevel()
    {
        $this->assertSame(0, $this->structureItem->level());

        $this->structure->rewind();
        $structureItem = $this->structure->current()->structure()->current();
        $this->assertSame(1, $structureItem->level());
    }

    public function testNavigation()
    {
        $this->assertSame([], $this->structureItem->navigation('de_AT'));
        $this->assertSame([], $this->structureItem->navigation('de_DE'));
        $this->assertSame([], $this->structureItem->navigation('en_US'));

        $this->structure->rewind();
        $structureItem = $this->structure->current()->structure()->current();
        $this->assertSame(['main'], $structureItem->navigation('de_AT'));
        $this->assertSame(['main', 'meta'], $structureItem->navigation('de_DE'));
        $this->assertSame(['meta'], $structureItem->navigation('en_US'));
    }

    public function testStructure()
    {
        $i = 0;
        foreach ($this->structureItem as $key => $item) {
            $this->assertSame(1, $item->level());
            $this->assertSame($i, $key);
            $i++;
        }
    }

    public function testCount()
    {
        $this->assertSame(3, $this->structureItem->count());
    }

    public function testOnly()
    {
        $structureItem = $this->structureItem->only(function () {
            return false;
        });

        $this->assertSame(0, $structureItem->count());

        $structureItem = $this->structureItem->only(function (StructureItem $structureItem) {
            return $structureItem->sitemapId() === '6674fe9a-867f-5fc5-b0aa-4793718c1528';
        });

        $this->assertSame(1, $structureItem->count());
        $structureItem->rewind();
        $first = $structureItem->current();
        $this->assertSame('6674fe9a-867f-5fc5-b0aa-4793718c1528', $first->sitemapId());
    }

    public function testParent()
    {
        $this->assertNull($this->structureItem->parent());

        $this->structure->rewind();
        $structureItem = $this->structure->current()->structure()->current();

        $this->assertSame($this->structureItem->sitemapId(), $structureItem->parent()->sitemapId());
    }
}
