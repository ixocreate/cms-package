<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Seo\Sitemap;

use Ixocreate\ServiceManager\NamedServiceInterface;

interface XmlSitemapProviderInterface extends NamedServiceInterface
{
    public function writeUrls(UrlsetCollector $urlset);

    public function writePingUrls(UrlsetCollector $urlset, \DateTime $fromDate);
}
