<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Application\Configurator\ConfiguratorInterface;
use Ixocreate\Application\Service\ServiceRegistryInterface;
use Ixocreate\Application\Service\SubManagerConfigurator;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Cms\Router\Replacement\ReplacementInterface;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Seo\Sitemap\PageProvider;
use Ixocreate\Cms\Seo\Sitemap\XmlSitemapProviderInterface;
use Ixocreate\Cms\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\Cms\Tree\Filter\FilterInterface;
use Ixocreate\Cms\Tree\FilterManager;

final class CmsConfigurator implements ConfiguratorInterface
{
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
    private $treeFilterSubManagerConfigurator;

    /**
     * @var SubManagerConfigurator
     */
    private $replacementManagerConfigurator;

    /**
     * CmsConfigurator constructor.
     */
    public function __construct()
    {
        $this->xmlSitemapSubManagerConfigurator = new SubManagerConfigurator(
            XmlSitemapProviderSubManager::class,
            XmlSitemapProviderInterface::class
        );
        $this->treeFilterSubManagerConfigurator = new SubManagerConfigurator(
            FilterManager::class,
            FilterInterface::class
        );
        $this->replacementManagerConfigurator = new SubManagerConfigurator(
            ReplacementManager::class,
            ReplacementInterface::class
        );
        $this->addXmlSitemapProvider(PageProvider::class);
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
    public function addTreeFilter(string $name, ?string $factory = null): void
    {
        $this->treeFilterSubManagerConfigurator->addService($name, $factory);
    }

    /**
     * @param string $name
     * @param string|null $factory
     */
    public function addRoutingReplacement(string $name, ?string $factory = null): void
    {
        $this->replacementManagerConfigurator->addService($name, $factory);
    }

    /**
     * @param ServiceRegistryInterface $serviceRegistry
     * @return void
     */
    public function registerService(ServiceRegistryInterface $serviceRegistry): void
    {
        $serviceRegistry->add(Config::class, new Config($this));

        $this->xmlSitemapSubManagerConfigurator->registerService($serviceRegistry);
        $this->treeFilterSubManagerConfigurator->registerService($serviceRegistry);
        $this->replacementManagerConfigurator->registerService($serviceRegistry);
    }
}
