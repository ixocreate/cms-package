<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Command\Seo;

use Ixocreate\Cms\Package\Seo\Sitemap\UrlsetCollector;
use Ixocreate\Cms\Package\Seo\Sitemap\XmlSitemapProviderInterface;
use Ixocreate\Cms\Package\Seo\Sitemap\XmlSitemapProviderSubManager;
use Ixocreate\CommandBus\Package\Command\AbstractCommand;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;

class GenerateSitemapCommand extends AbstractCommand
{
    /**
     * @var XmlSitemapProviderSubManager
     */
    private $subManager;

    /**
     * GenerateSitemapCommand constructor.
     * @param XmlSitemapProviderSubManager $subManager
     */
    public function __construct(XmlSitemapProviderSubManager $subManager)
    {
        $this->subManager = $subManager;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        $dataSitemap = 'data/sitemap_temp/';
        if (!\is_dir($dataSitemap)) {
            \mkdir($dataSitemap, 0777, true);
        }


        $sitemaps = [];

        foreach ($this->subManager->getServices() as $serviceName) {
            /** @var XmlSitemapProviderInterface $provider */
            $provider = $this->subManager->get($serviceName);

            $urlset = new UrlsetCollector();
            $provider->writeUrls($urlset);

            $savePath = $dataSitemap . $provider::serviceName() . '/';
            if (!\is_dir($savePath)) {
                \mkdir($savePath);
            }

            foreach ($urlset->getCollections() as $key => $collection) {
                $driver = new XmlWriterDriver();
                $collection->accept($driver);

                $filename =  $provider::serviceName() . ($key + 1) . ".xml";
                $sitemaps[] = [
                    'provider' => $provider::serviceName(),
                    'createdAt' => (new \DateTime())->format('c'),
                    'filename' => $provider::serviceName() . '/' . $filename,
                ];

                \file_put_contents($savePath . $filename, $driver->output());
            }
        }

        \file_put_contents('data/sitemap_temp/sitemap.json', \json_encode($sitemaps));

        if (\is_dir('data/sitemap')) {
            \rename('data/sitemap', 'data/sitemap_del');
            $this->deleteSitemap('data/sitemap_del');
        }
        \rename('data/sitemap_temp', 'data/sitemap');
        return true;
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'cms-sitemap-generate';
    }

    /**
     * @param $dir
     */
    private function deleteSitemap($dir)
    {
        if (\is_dir($dir)) {
            $objects = \scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (\is_dir($dir . "/" . $object)) {
                        $this->deleteSitemap($dir . "/" . $object);
                    } else {
                        \unlink($dir . "/" . $object);
                    }
                }
            }
            \rmdir($dir);
        }
    }
}
