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
use Ixocreate\Cms\Site\Tree\SearchInterface;
use Ixocreate\Cms\Site\Tree\SearchSubManager;
use Ixocreate\Cms\Strategy\Full\Strategy;
use Ixocreate\Cms\Tree\Mutatable\MutatableInterface;
use Ixocreate\Cms\Tree\Mutatable\MutatableSubManager;
use Ixocreate\Cms\Tree\Searchable\SearchableInterface;
use Ixocreate\Cms\Tree\Searchable\SearchableSubManager;

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
    private $treeSearchSubManagerConfigurator;

    /**
     * @var SubManagerConfigurator
     */
    private $replacementManagerConfigurator;

    /**
     * @var string
     */
    private $strategy = Strategy::class;

    /**
     * @var SubManagerConfigurator
     */
    private $mutatableSubManagerConfigurator;

    /**
     * @var SubManagerConfigurator
     */
    private $searchablehSubManagerConfigurator;

    /**
     * CmsConfigurator constructor.
     */
    public function __construct()
    {
        $this->xmlSitemapSubManagerConfigurator = new SubManagerConfigurator(
            XmlSitemapProviderSubManager::class,
            XmlSitemapProviderInterface::class
        );
        $this->treeSearchSubManagerConfigurator = new SubManagerConfigurator(
            SearchSubManager::class,
            SearchInterface::class
        );
        $this->mutatableSubManagerConfigurator = new SubManagerConfigurator(
            MutatableSubManager::class,
            MutatableInterface::class
        );
        $this->searchablehSubManagerConfigurator = new SubManagerConfigurator(
            SearchableSubManager::class,
            SearchableInterface::class
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

    public function setStrategy(string $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function enableFullStrategy(): void
    {
        $this->strategy = Strategy::class;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
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
     * @param string $name
     * @param string|null $factory
     */
    public function addSearchable(string $name, ?string $factory = null): void
    {
        $this->searchablehSubManagerConfigurator->addService($name, $factory);
    }

    /**
     * @param string $name
     * @param string|null $factory
     */
    public function addMutatable(string $name, ?string $factory = null): void
    {
        $this->mutatableSubManagerConfigurator->addService($name, $factory);
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
        $this->treeSearchSubManagerConfigurator->registerService($serviceRegistry);
        $this->replacementManagerConfigurator->registerService($serviceRegistry);
        $this->mutatableSubManagerConfigurator->registerService($serviceRegistry);
        $this->searchablehSubManagerConfigurator->registerService($serviceRegistry);
    }
}
