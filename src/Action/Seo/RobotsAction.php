<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Action\Seo;

use Ixocreate\Package\Cms\Config\Config;
use Ixocreate\Package\ProjectUri\ProjectUri;
use Ixocreate\Package\Template\TemplateResponse;
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
     * @var ProjectUri
     */
    private $projectUri;

    /**
     * RobotsAction constructor.
     * @param Config $config
     * @param ProjectUri $projectUri
     */
    public function __construct(Config $config, ProjectUri $projectUri)
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
