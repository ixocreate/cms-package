<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Seo\Sitemap;

use Ixocreate\Contract\ServiceManager\NamedServiceInterface;

interface XmlSitemapProviderInterface extends NamedServiceInterface
{
    public function writeUrls(UrlsetCollector $urlset);

    public function writePingUrls(UrlsetCollector $urlset, \DateTime $fromDate);
}
