<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Config;

use Ixocreate\Cms\Seo\Sitemap\PageProvider;
use Ixocreate\Cms\Seo\Sitemap\XmlSitemapProviderInterface;
use Ixocreate\Cms\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\Cms\Site\Tree\SearchInterface;
use Ixocreate\Cms\Site\Tree\SearchSubManager;
use Ixocreate\Contract\Application\ConfiguratorInterface;
use Ixocreate\Contract\Application\ServiceRegistryInterface;
use Ixocreate\ServiceManager\SubManager\SubManagerConfigurator;

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
     * @var SubManagerConfigurator
     */
    private $xmlSitemapSubManagerConfigurator;

    /**
     * @var bool
     */
    private $robotsNoIndex = false;

    /**
     * @var string
     */
    private $robotsTemplate = 'seo::robotstxt';

    /**
     * @var SubManagerConfigurator
     */
    private $treeSearchSubManagerConfigurator;

    /**
     * Configurator constructor.
     */
    public function __construct()
    {
        $this->xmlSitemapSubManagerConfigurator = new SubManagerConfigurator(XmlSitemapProviderSubManager::class, XmlSitemapProviderInterface::class);
        $this->treeSearchSubManagerConfigurator = new SubManagerConfigurator(SearchSubManager::class, SearchInterface::class);
        $this->addXmlSitemapProvider(PageProvider::class);
    }

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
        return \array_values($this->navigation);
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
     * @return bool
     */
    public function getRobotsNoIndex(): bool
    {
        return $this->robotsNoIndex;
    }

    /**
     * @param bool $robotsNoIndex
     */
    public function setRobotsNoIndex(bool $robotsNoIndex): void
    {
        $this->robotsNoIndex = $robotsNoIndex;
    }

    /**
     * @return string
     */
    public function getRobotsTemplate(): string
    {
        return $this->robotsTemplate;
    }

    /**
     * @param string $robotsTemplate
     */
    public function setRobotsTemplate(string $robotsTemplate)
    {
        $this->robotsTemplate = $robotsTemplate;
    }

    /**
     * @param string $name
     * @param string|null $factory
     */
    public function addXmlSitemapProvider(string $name, ?string $factory = null)
    {
        $this->xmlSitemapSubManagerConfigurator->addService($name, $factory);
    }

    /**
     * @param string $name
     * @param string|null $factory
     */
    public function addTreeSearchable(string $name, ?string $factory = null): void
    {
        $this->treeSearchSubManagerConfigurator->addService($name, $factory);
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     * @return void
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(Config::class, new Config($this));

        $this->xmlSitemapSubManagerConfigurator->registerService($serviceRegistry);
        $this->treeSearchSubManagerConfigurator->registerService($serviceRegistry);
    }
}
