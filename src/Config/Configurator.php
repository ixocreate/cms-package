<?php
namespace Ixocreate\Cms\Config;

use Ixocreate\Contract\Application\ConfiguratorInterface;
use Ixocreate\Contract\Application\ServiceRegistryInterface;

final class Configurator implements ConfiguratorInterface
{
    /**
     * @var string
     */
    private $localizationUrlSchema = '%MAIN_URL%/%LANG%';

    /**
     * @var string|null
     */
    private $defaultBaseUrl = '%MAIN_URL%';

    /**
     * @var array
     */
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
     * @param string $defaultBaseUrl
     */
    public function setDefaultBaseUrl(?string $defaultBaseUrl): void
    {
        $this->defaultBaseUrl = $defaultBaseUrl;
    }

    /**
     * @return string
     */
    public function getDefaultBaseUrl(): ?string
    {
        return $this->defaultBaseUrl;
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
