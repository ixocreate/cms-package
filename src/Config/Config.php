<?php
namespace KiwiSuite\Cms\Config;

use KiwiSuite\Contract\Application\SerializableServiceInterface;

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

    public function __construct(Configurator $configurator)
    {
        $this->localizationUrlSchema = $configurator->getLocalizationUrlSchema();
        $this->navigation = $configurator->getNavigation();
    }


    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'localizationUrlSchema' => $this->localizationUrlSchema,
            'navigation' => $this->navigation,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this->navigation = $unserialized['navigation'];
        $this->localizationUrlSchema = $unserialized['localizationUrlSchema'];
    }
}
