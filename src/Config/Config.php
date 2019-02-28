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

    public function defaultBaseUrl(): ?string
    {
        return $this->defaultBaseUrl;
    }

    public function robotsNoIndex(): bool
    {
        return $this->robotsNoIndex;
    }

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
            'defaultBaseUrl' => $this->defaultBaseUrl,
            'navigation' => $this->navigation,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialized = \unserialize($serialized);

        $this->navigation = $unserialized['navigation'];
        $this->defaultBaseUrl = $unserialized['defaultBaseUrl'];
        $this->localizationUrlSchema = $unserialized['localizationUrlSchema'];
    }
}
