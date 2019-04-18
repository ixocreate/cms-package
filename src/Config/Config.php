<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Config;

use Ixocreate\Application\Service\SerializableServiceInterface;

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
     * Config constructor.
     * @param Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        $this->navigation = $configurator->getNavigation();
        $this->robotsNoIndex = $configurator->getRobotsNoIndex();
        $this->robotsTemplate = $configurator->getRobotsTemplate();
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
    public function serialize()
    {
        return \serialize([
            'navigation' => $this->navigation,
            'robotsNoIndex' => $this->robotsNoIndex,
            'robotsTemplate' => $this->robotsTemplate,
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
    }
}
