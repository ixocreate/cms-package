<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms\Cacheable;

use Ixocreate\Cms\Cacheable\CompiledGeneratorRoutesCacheable;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\Replacement\ParentReplacement;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Router\Replacement\SlugReplacement;
use Ixocreate\Cms\Tree\FilterManager;
use Ixocreate\Cms\Tree\Structure\StructureBuilder;
use Ixocreate\Cms\Tree\Structure\StructureStore;
use Ixocreate\Intl\LocaleConfigurator;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\UuidType;
use Ixocreate\Test\Schema\TypeMockHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ixocreate\Cms\Cacheable\CompiledGeneratorRoutesCacheable
 * @runTestsInSeparateProcesses
 */
class CompiledGeneratorRoutesCacheableTest extends TestCase
{
    /**
     * @var CompiledGeneratorRoutesCacheable
     */
    private $cacheable;

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

        $localeConfigurator = new LocaleConfigurator();
        $localeConfigurator->add('de_AT');
        $localeConfigurator->add('de_DE');
        $localeConfigurator->add('en_US');
        $localeConfigurator->setDefaultLocale('de_DE');
        $localeManager = new LocaleManager($localeConfigurator);

        $pageTypeSubManager = $this->createMock(PageTypeSubManager::class);
        $pageTypeSubManager->method('get')->willReturn($this->createMock(PageTypeInterface::class));

        $filterManager = $this->createMock(FilterManager::class);

        $replacementSubManager = $this->createMock(ReplacementManager::class);
        $replacements = [
            new ParentReplacement(),
            new SlugReplacement(),
        ];
        $replacementSubManager->method('replacementServices')->willReturn($replacements);

        $structureBuilder = $this->createMock(StructureBuilder::class);
        $structureBuilder->method('build')->willReturnCallback(function () {
            return (new StructureStore(include 'tree.php'))->structure();
        });

        $this->cacheable = new CompiledGeneratorRoutesCacheable(
            $localeManager,
            $structureBuilder,
            $pageTypeSubManager,
            $replacementSubManager,
            $filterManager
        );
    }

    public function testUncachedResult()
    {
        $result = $this->cacheable->uncachedResult();
        $this->assertIsArray($result);
        $this->assertCount(10483, $result);

        $this->assertArrayHasKey('page.9b4235f6-51fd-547d-8475-0332bc3ed8ff', $result);
        $this->assertArrayHasKey('page.f193735a-de28-5ebe-95a3-8214e2b94dff', $result);
        $this->assertArrayHasKey('page.bf175a54-47b5-51c7-96ec-15308ffe85af', $result);
    }

    public function testSettings()
    {
        $this->assertSame('compiled.url.generator', $this->cacheable->cacheKey());
        $this->assertSame('cms_store', $this->cacheable->cacheName());
        $this->assertSame(0, $this->cacheable->cacheTtl());
    }
}
