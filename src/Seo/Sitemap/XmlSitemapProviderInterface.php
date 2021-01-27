<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Seo\Sitemap;

use Ixocreate\Application\ServiceManager\NamedServiceInterface;

interface XmlSitemapProviderInterface extends NamedServiceInterface
{
    public function writeUrls(UrlsetCollector $urlset);

    public function writePingUrls(UrlsetCollector $urlset, \DateTime $fromDate);
}
