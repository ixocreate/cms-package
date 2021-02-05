<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Schema\Link;

use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Link\SitemapLink;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\UuidType;
use Ixocreate\Test\Schema\TypeMockHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * @covers \Ixocreate\Cms\Link\SitemapLink
 */
class SitemapLinkTest extends TestCase
{
    private $adminConfig;

    private $page;

    private $pageRepository;

    private $pageRoute;

    public function setUp(): void
    {
        $this->adminConfig =  $this->createMock(AdminConfig::class);

        (new TypeMockHelper($this, [
            UuidType::class => new UuidType(),
            UuidType::serviceName() => new UuidType(),
            DateTimeType::class => new DateTimeType(),
            DateTimeType::serviceName() => new DateTimeType(),
        ], false))->create();

        $this->page = new Page([
            'id' => '84456422-0d2a-43be-b766-5b7d09d0b0f6',
            'sitemapId' => '84456422-0d2a-43be-b766-5b7d09d0b0f6',
            'locale' => 'de_AT',
            'name' => 'test',
            'slug' => 'test',
            'publishedFrom' => null,
            'publishedUntil' => null,
            'status' => 'online',
            'createdAt' => '2016-02-04 16:37:00',
            'updatedAt' => '2018-08-10 05:41:00',
            'releasedAt' => '2018-08-10 05:41:00',
        ]);

        $this->pageRepository = $this->createMock(PageRepository::class);
        $this->pageRepository->method('find')->willReturnCallback(function ($id) {
            if ($id === '84456422-0d2a-43be-b766-5b7d09d0b0f6') {
                return $this->page;
            }

            if ($id === 'route_not_found_exception') {
                return new Page([
                    'id' => 'accfc52c-4670-452f-b1a8-e5f5f0bc5ee6',
                    'sitemapId' => 'accfc52c-4670-452f-b1a8-e5f5f0bc5ee6',
                    'locale' => 'de_AT',
                    'name' => 'test',
                    'slug' => 'test',
                    'publishedFrom' => null,
                    'publishedUntil' => null,
                    'status' => 'online',
                    'createdAt' => '2016-02-04 16:37:00',
                    'updatedAt' => '2018-08-10 05:41:00',
                    'releasedAt' => '2018-08-10 05:41:00',
                ]);
            }

            return null;
        });

        $this->pageRoute = $this->createMock(PageRoute::class);
        $this->pageRoute->method('fromPage')->willReturnCallback(function (Page $page) {
            if ((string) $page->id() === '84456422-0d2a-43be-b766-5b7d09d0b0f6') {
                return 'https://www.ixocreate.com/test';
            }

            throw new RouteNotFoundException();
        });
    }

    public function testServiceName()
    {
        $this->assertSame('sitemap', SitemapLink::serviceName());
    }

    public function testLabel()
    {
        $this->assertSame('Sitemap', (new SitemapLink($this->adminConfig, $this->pageRepository, $this->pageRoute))->label());
    }

    public function testCreate()
    {
        $pageLink = new SitemapLink($this->adminConfig, $this->pageRepository, $this->pageRoute);

        $newPageLink = $pageLink->create((string) $this->page->id());
        $this->assertNotSame($newPageLink, $pageLink);
        $this->assertSame((string) $this->page->id(), (string)$newPageLink->toJson()['id']);

        $newPageLink = $pageLink->create(['id' => (string) $this->page->id()]);
        $this->assertNotSame($newPageLink, $pageLink);
        $this->assertSame((string) $this->page->id(), (string)$newPageLink->toJson()['id']);

        $clonedPageLink = $pageLink->create($newPageLink);
        $this->assertNotSame($clonedPageLink, $newPageLink);
        $this->assertSame((string) $this->page->id(), (string)$clonedPageLink->toJson()['id']);

        $newPageLink = $pageLink->create('');
        $this->assertNotSame($newPageLink, $pageLink);
        $this->assertNull($newPageLink->toJson());

        $newPageLink = $pageLink->create([]);
        $this->assertNotSame($newPageLink, $pageLink);
        $this->assertNull($newPageLink->toJson());

        $newPageLink = $pageLink->create(['id' => 'dont_exist']);
        $this->assertNotSame($newPageLink, $pageLink);
        $this->assertNull($newPageLink->toJson());

        $newPageLink = $pageLink->create('dont_exist');
        $this->assertNotSame($newPageLink, $pageLink);
        $this->assertNull($newPageLink->toJson());
    }

    public function testToJson()
    {
        $pageLink = new SitemapLink($this->adminConfig, $this->pageRepository, $this->pageRoute);

        $newPageLink = $pageLink->create((string) $this->page->id());
        $this->assertSame($this->page->toPublicArray(), $newPageLink->toJson());

        $newPageLink = $pageLink->create('doesnt_exist');
        $this->assertNull($newPageLink->toJson());
    }

    public function testToDatabase()
    {
        $pageLink = new SitemapLink($this->adminConfig, $this->pageRepository, $this->pageRoute);

        $newPageLink = $pageLink->create((string) $this->page->id());
        $this->assertSame((string) $this->page->id(), $newPageLink->toDatabase());

        $newPageLink = $pageLink->create('doesnt_exist');
        $this->assertNull($newPageLink->toDatabase());
    }

    public function testAssemble()
    {
        $pageLink = new SitemapLink($this->adminConfig, $this->pageRepository, $this->pageRoute);

        $newPageLink = $pageLink->create((string) $this->page->id());
        $this->assertSame('https://www.ixocreate.com/test', $newPageLink->assemble());

        $newPageLink = $pageLink->create('doesnt_exist');
        $this->assertSame('', $newPageLink->assemble());

        $newPageLink = $pageLink->create('route_not_found_exception');
        $this->assertSame('', $newPageLink->assemble());
    }

    public function testSerialize()
    {
        $pageLink = new SitemapLink($this->adminConfig, $this->pageRepository, $this->pageRoute);
        $pageLink = $pageLink->create((string) $this->page->id());
        $pageLink = \unserialize(\serialize($pageLink));

        $this->assertSame((string) $this->page->id(), $pageLink->toDatabase());
    }
}
