<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Config;

use Ixocreate\Contract\Application\SerializableServiceInterface;

final class Config implements SerializableServiceInterface
{
    /**
     * @var string
     */
    private $localizationUrlSchema;

    /**
     * @var array
     */
    private $navigation = [];

    /**
     * @var string
     */
    private $defaultBaseUrl;

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
        $this->localizationUrlSchema = $configurator->getLocalizationUrlSchema();
        $this->navigation = $configurator->getNavigation();
        $this->defaultBaseUrl = $configurator->getDefaultBaseUrl();
        $this->robotsNoIndex = $configurator->getRobotsNoIndex();
        $this->robotsTemplate = $configurator->getRobotsTemplate();
    }

    /**
     * @return string
     */
    public function localizationUrlSchema(): string
    {
        return $this->localizationUrlSchema;
    }

    /**
     * @return array
     */
    public function navigation(): array
    {
        return $this->navigation;
    }

    /**
     * @return string|null
     */
    public function defaultBaseUrl(): ?string
    {
        return $this->defaultBaseUrl;
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
            'localizationUrlSchema' => $this->localizationUrlSchema,
            'navigation' => $this->navigation,
            'defaultBaseUrl' => $this->defaultBaseUrl,
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

        $this->localizationUrlSchema = $unserialized['localizationUrlSchema'];
        $this->navigation = $unserialized['navigation'];
        $this->defaultBaseUrl = $unserialized['defaultBaseUrl'];
        $this->robotsNoIndex = $unserialized['robotsNoIndex'];
        $this->robotsTemplate = $unserialized['robotsTemplate'];
    }
}
