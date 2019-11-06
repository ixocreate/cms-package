<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Site\Tree;

use Ixocreate\Application\Service\ServiceManagerConfig;
use Ixocreate\Application\Service\ServiceManagerConfigurator;
use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Application\Uri\ApplicationUriConfigurator;
use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\CmsConfigurator;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Structure\Structure;
use Ixocreate\Cms\Site\Tree\Container;
use Ixocreate\Cms\Site\Tree\Item;
use Ixocreate\Cms\Site\Tree\ItemFactory;
use Ixocreate\Cms\Site\Tree\Search\ActiveSearch;
use Ixocreate\Cms\Site\Tree\Search\CallableSearch;
use Ixocreate\Cms\Site\Tree\Search\HandleSearch;
use Ixocreate\Cms\Site\Tree\Search\MaxLevelSearch;
use Ixocreate\Cms\Site\Tree\Search\MinLevelSearch;
use Ixocreate\Cms\Site\Tree\Search\NavigationSearch;
use Ixocreate\Cms\Site\Tree\Search\OnlineSearch;
use Ixocreate\Cms\Site\Tree\SearchInterface;
use Ixocreate\Cms\Site\Tree\SearchSubManager;
use Ixocreate\ServiceManager\ServiceManager;
use Ixocreate\ServiceManager\ServiceManagerSetup;
use Ixocreate\ServiceManager\SubManager\SubManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;

class ContainerTest extends TestCase
{
    private function generateStructure(): Structure
    {
        $tree = [
            [
                'sitemapId' => '031117f8-9795-4e10-ace0-3a57f5d2f166',
                'handle' => null,
                'pages' => [
                    'de_AT' => 'df37d84d-7ff2-4a3d-bf60-c62386f92783',
                    'en_US' => '54da0e10-bcec-4f83-bd34-339fe05dfc06',
                ],
                'navigation' => ['main1', 'main2'],
                'children' => [
                    [
                        'sitemapId' => '4e1867a2-d396-4aab-aec6-2e4beb19103d',
                        'handle' => null,
                        'pages' => [
                            'de_AT' => 'f5ce965d-530e-41d2-a605-a6b105af7fad',
                            'en_US' => 'a4ccfa7f-4681-4ab9-ad64-e8e4190d7a39',
                        ],
                        'navigation' => ['main1'],
                        'children' => [

                        ],
                    ],
                    [
                        'sitemapId' => 'd3a88fab-1b31-45e1-ad1f-b5a4fecb01c8',
                        'handle' => null,
                        'pages' => [
                            'de_AT' => '30a92384-5223-46e1-9320-876b50e80b84',
                            'en_US' => 'a90f0508-14f5-4649-a0be-6d8a3673eb2a',
                        ],
                        'navigation' => ['main2'],
                        'children' => [
                            [
                                'sitemapId' => '0426274a-3e58-4fe6-9b0b-b7c2b881f01d',
                                'handle' => null,
                                'pages' => [
                                    'de_AT' => '31e31603-fd91-4a5b-baa4-b477f72f2688',
                                    'en_US' => '36f1294b-cee3-4423-9cce-1c6800dbe859',
                                ],
                                'navigation' => ['main2'],
                                'children' => [

                                ],
                            ],
                            [
                                'sitemapId' => 'b888538c-b632-4c9e-88ba-b577fd041972',
                                'handle' => null,
                                'pages' => [
                                    'de_AT' => '804c0c88-1f9a-48ca-b9ab-b3a33d39a1b9',
                                    'en_US' => '108f98ba-2717-4f6d-95d0-a4f38cf161af',
                                ],
                                'navigation' => ['main1'],
                                'children' => [

                                ],
                            ],
                        ],
                    ],
                    [
                        'sitemapId' => '58b99ef4-47dd-4333-802d-cb30b21d4412',
                        'handle' => null,
                        'pages' => [
                            'de_AT' => 'fd500189-1099-4a3a-87f5-ed05906dd5c5',
                            'en_US' => '4f167e6d-9822-4524-84a7-0cbf38829b6b',
                        ],
                        'navigation' => ['main1'],
                        'children' => [

                        ],
                    ],
                    [
                        'sitemapId' => '2b110284-d3c0-49e0-8d0a-9364463f0f81',
                        'handle' => null,
                        'pages' => [
                            'de_AT' => '7accd01e-e7e2-4349-8f29-790bc7a16196',
                            'en_US' => '258724f4-aa0f-41b9-861f-2e32d51eef29',
                        ],
                        'navigation' => ['main1'],
                        'children' => [

                        ],
                    ],
                ],
            ],
        ];
        return new Structure($tree);
    }

    private function mockCacheableInterface(): CacheableInterface
    {
        return $this->createMock(CacheableInterface::class);
    }

    private function mockContainerInterface(): \Psr\Container\ContainerInterface
    {
        return $this->createMock(\Psr\Container\ContainerInterface::class);
    }

    private function mockMiddlewareContainer(): MiddlewareContainer
    {
        return $this->createMock(MiddlewareContainer::class);
    }

    private function mockSubManagerInterface(): SubManagerInterface
    {
        return $this->createMock(SubManagerInterface::class);
    }

    private function getDefaultContainer(): Container
    {
        $serviceManagerConfigurator = new ServiceManagerConfigurator();
        $serviceManagerConfigurator->addService(ActiveSearch::class);
        $serviceManagerConfigurator->addService(CallableSearch::class);
        $serviceManagerConfigurator->addService(HandleSearch::class);
        $serviceManagerConfigurator->addService(MaxLevelSearch::class);
        $serviceManagerConfigurator->addService(MinLevelSearch::class);
        $serviceManagerConfigurator->addService(NavigationSearch::class);
        $serviceManagerConfigurator->addService(OnlineSearch::class);

        $searchSubManager = new SearchSubManager(
            new ServiceManager(new ServiceManagerConfig(new ServiceManagerConfigurator()), new ServiceManagerSetup()),
            $serviceManagerConfigurator->getServiceManagerConfig(),
            SearchInterface::class
        );

        $applicationUri = new ApplicationUri(new ApplicationUriConfigurator());

        $itemFactory = new ItemFactory(
            $this->mockCacheableInterface(),
            $this->mockCacheableInterface(),
            $this->mockCacheableInterface(),
            new CacheManager($this->mockContainerInterface()),
            $this->mockSubManagerInterface(),
            $searchSubManager,
            new PageRoute(
                new Config(new CmsConfigurator()),
                new CmsRouter(
                    new RouteCollection(),
                    new MiddlewareFactory($this->mockMiddlewareContainer()),
                    $applicationUri
                ),
                $applicationUri
            )
        );

        return new Container($this->generateStructure()->structure(), $searchSubManager, $itemFactory);
    }

    /**
     * @covers \Ixocreate\Cms\Site\Tree\Container::filter
     */
    public function testFilter()
    {
        $filterIds = [
            "031117f8-9795-4e10-ace0-3a57f5d2f166",
            "4e1867a2-d396-4aab-aec6-2e4beb19103d",
            "d3a88fab-1b31-45e1-ad1f-b5a4fecb01c8",
            "58b99ef4-47dd-4333-802d-cb30b21d4412",
            "2b110284-d3c0-49e0-8d0a-9364463f0f81",
            "0426274a-3e58-4fe6-9b0b-b7c2b881f01d",
        ];
        $container = $this->getDefaultContainer()
            ->filter(function (Item $item) use ($filterIds) {
                return \in_array($item->structureItem()->sitemapId(), $filterIds);
            })
            ->flatten();
        $selectedItems = [];
        /** @var Item $item */
        foreach ($container as $item) {
            $selectedItems[] = $item->structureItem()->sitemapId();
        }
        $this->assertSame($filterIds, \array_intersect($filterIds, $selectedItems));
        $this->assertSame([], \array_diff($filterIds, $selectedItems));
    }

    /**
     * @covers \Ixocreate\Cms\Site\Tree\Container::withMaxLevel
     */
    public function testWithMaxLevel()
    {
        $filterIds = [
            "031117f8-9795-4e10-ace0-3a57f5d2f166",
            "4e1867a2-d396-4aab-aec6-2e4beb19103d",
            "d3a88fab-1b31-45e1-ad1f-b5a4fecb01c8",
            "58b99ef4-47dd-4333-802d-cb30b21d4412",
            "2b110284-d3c0-49e0-8d0a-9364463f0f81",
        ];
        $container = $this->getDefaultContainer()
            ->withMaxLevel(1)
            ->flatten();
        $selectedItems = [];
        /** @var Item $item */
        foreach ($container as $item) {
            $selectedItems[] = $item->structureItem()->sitemapId();
        }
        $this->assertSame($filterIds, \array_intersect($filterIds, $selectedItems));
        $this->assertSame([], \array_diff($filterIds, $selectedItems));


        $filterIds = [
            "031117f8-9795-4e10-ace0-3a57f5d2f166",
        ];
        $container = $this->getDefaultContainer()
            ->withMaxLevel(0)
            ->flatten();
        $selectedItems = [];
        /** @var Item $item */
        foreach ($container as $item) {
            $selectedItems[] = $item->structureItem()->sitemapId();
        }
        $this->assertSame($filterIds, \array_intersect($filterIds, $selectedItems));
        $this->assertSame([], \array_diff($filterIds, $selectedItems));
    }

    /**
     * @covers \Ixocreate\Cms\Site\Tree\Container::withNavigation
     */
    public function testWithNavigation()
    {
        $filterIds = [
            "031117f8-9795-4e10-ace0-3a57f5d2f166",
            "4e1867a2-d396-4aab-aec6-2e4beb19103d",
            "58b99ef4-47dd-4333-802d-cb30b21d4412",
            "2b110284-d3c0-49e0-8d0a-9364463f0f81",
        ];
        $container = $this->getDefaultContainer()
            ->withNavigation("main1")
            ->flatten();
        $selectedItems = [];
        /** @var Item $item */
        foreach ($container as $item) {
            $selectedItems[] = $item->structureItem()->sitemapId();
        }
        $this->assertSame($filterIds, \array_intersect($filterIds, $selectedItems));
        $this->assertSame([], \array_diff($filterIds, $selectedItems));

        $filterIds = [
            "031117f8-9795-4e10-ace0-3a57f5d2f166",
            "d3a88fab-1b31-45e1-ad1f-b5a4fecb01c8",
            "0426274a-3e58-4fe6-9b0b-b7c2b881f01d",
        ];
        $container = $this->getDefaultContainer()
            ->withNavigation("main2")
            ->flatten();
        $selectedItems = [];
        /** @var Item $item */
        foreach ($container as $item) {
            $selectedItems[] = $item->structureItem()->sitemapId();
        }
        $this->assertSame($filterIds, \array_intersect($filterIds, $selectedItems));
        $this->assertSame([], \array_diff($filterIds, $selectedItems));
    }
}
