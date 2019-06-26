<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms\Cacheable;

use Ixocreate\Cms\Cacheable\CompiledMatcherRoutesCacheable;
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
 * @covers \Ixocreate\Cms\Cacheable\CompiledMatcherRoutesCacheable
 * @runTestsInSeparateProcesses
 */
class CompiledMatcherRoutesCacheableTest extends TestCase
{
    /**
     * @var CompiledMatcherRoutesCacheable
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

        $this->cacheable = new CompiledMatcherRoutesCacheable(
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
    }

    public function testSettings()
    {
        $this->assertSame('compiled.url.matcher', $this->cacheable->cacheKey());
        $this->assertSame('cms_store', $this->cacheable->cacheName());
        $this->assertSame(0, $this->cacheable->cacheTtl());
    }
}
