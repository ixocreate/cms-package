<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Console;

use Ixocreate\Application\Console\CommandInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\CompiledMatcherRoutesCacheable;
use Ixocreate\Cms\Router\RouteCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRouterMatcherCacheConsole extends Command implements CommandInterface
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var CompiledMatcherRoutesCacheable
     */
    private $compiledMatcherRoutesCacheable;

    /**
     * GenerateRoutingConsole constructor.
     * @param RouteCollection $routeCollection
     * @param CacheManager $cacheManager
     * @param CompiledMatcherRoutesCacheable $compiledMatcherRoutesCacheable
     */
    public function __construct(
        RouteCollection $routeCollection,
        CacheManager $cacheManager,
        CompiledMatcherRoutesCacheable $compiledMatcherRoutesCacheable
    ) {
        parent::__construct(self::getCommandName());
        $this->routeCollection = $routeCollection;
        $this->cacheManager = $cacheManager;
        $this->compiledMatcherRoutesCacheable = $compiledMatcherRoutesCacheable;
    }

    public static function getCommandName()
    {
        return 'cms:generate-router-matcher-cache';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \ini_set('memory_limit', '2048M');
        \set_time_limit(0);

        $routeCollection = $this->routeCollection->build();

        $this->cacheManager->fetch(
            $this->compiledMatcherRoutesCacheable->withRouteCollection($routeCollection),
            true
        );
    }
}
