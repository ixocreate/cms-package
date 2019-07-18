<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy;

use Ixocreate\Cms\Config\Config;
use Ixocreate\ServiceManager\FactoryInterface;
use Ixocreate\ServiceManager\ServiceManagerInterface;

final class StrategyFactory implements FactoryInterface
{

    /**
     * @param ServiceManagerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ServiceManagerInterface $container, $requestedName, array $options = null)
    {
        return new Strategy(
            $container->get($container->get(Config::class)->strategy())
        );
    }
}
