<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Seo;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Cms\Config\Config;
use Ixocreate\Template\TemplateResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RobotsAction implements MiddlewareInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ApplicationUri
     */
    private $projectUri;

    /**
     * RobotsAction constructor.
     *
     * @param Config $config
     * @param ApplicationUri $projectUri
     */
    public function __construct(Config $config, ApplicationUri $projectUri)
    {
        $this->config = $config;
        $this->projectUri = $projectUri;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sitemapUrl = $this->projectUri->getMainUri() . '/sitemap/sitemap.xml';

        return new TemplateResponse(
            $this->config->robotsTemplate(),
            ['noIndex' => $this->config->robotsNoIndex(), 'sitemapUrl' => $sitemapUrl],
            [],
            200,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }
}
