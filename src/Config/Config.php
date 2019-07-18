<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Config;

use Ixocreate\Application\Service\SerializableServiceInterface;
use Ixocreate\Cms\CmsConfigurator;

final class Config implements SerializableServiceInterface
{
    /**
     * @var array
     */
    private $navigation = [];

    /**
     * @var bool
     */
    private $robotsNoIndex;

    /**
     * @var string
     */
    private $robotsTemplate;

    /**
     * @var
     */
    private $strategy;

    /**
     * Config constructor.
     *
     * @param CmsConfigurator $configurator
     */
    public function __construct(CmsConfigurator $configurator)
    {
        $this->navigation = $configurator->getNavigation();
        $this->robotsNoIndex = $configurator->getRobotsNoIndex();
        $this->robotsTemplate = $configurator->getRobotsTemplate();
        $this->strategy = $configurator->getStrategy();
    }

    /**
     * @return array
     */
    public function navigation(): array
    {
        return $this->navigation;
    }

    /**
     * @return bool
     */
    public function robotsNoIndex(): bool
    {
        return $this->robotsNoIndex;
    }

    /**
     * @return string
     */
    public function robotsTemplate(): string
    {
        return $this->robotsTemplate;
    }

    /**
     * @return string
     */
    public function strategy(): string
    {
        return $this->strategy;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return \serialize([
            'navigation' => $this->navigation,
            'robotsNoIndex' => $this->robotsNoIndex,
            'robotsTemplate' => $this->robotsTemplate,
            'strategy' => $this->strategy,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialized = \unserialize($serialized);

        $this->navigation = $unserialized['navigation'];
        $this->robotsNoIndex = $unserialized['robotsNoIndex'];
        $this->robotsTemplate = $unserialized['robotsTemplate'];
        $this->strategy = $unserialized['strategy'];
    }
}
