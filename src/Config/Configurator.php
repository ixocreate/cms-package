<?php
namespace KiwiSuite\Cms\Config;

use KiwiSuite\Contract\Application\ConfiguratorInterface;
use KiwiSuite\Contract\Application\ServiceRegistryInterface;

final class Configurator implements ConfiguratorInterface
{
    /**
     * @var string
     */
    private $localizationUrlSchema = '/${LANG}';

    private $navigation = [];

    /**
     * @param string $localizationUrlSchema
     */
    public function setLocalizationUrlSchema(string $localizationUrlSchema): void
    {
        $this->localizationUrlSchema = $localizationUrlSchema;
    }

    /**
     * @return string
     */
    public function getLocalizationUrlSchema(): string
    {
        return $this->localizationUrlSchema;
    }

    /**
     * @param string $name
     * @param string $label
     */
    public function addNavigation(string $name, string $label): void
    {
        $this->navigation[$name] = [
            'name' => $name,
            'label' => $label,
        ];
    }

    /**
     * @return array
     */
    public function getNavigation(): array
    {
        return array_values($this->navigation);
    }


    /**
     * @param ServiceRegistryInterface $serviceRegistry
     * @return void
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(Config::class, new Config($this));
    }
}
