<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Action\Seo;

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

    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        return new TemplateResponse($this->config->robotsTemplate(), ['noIndex' => $this->config->robotsNoIndex(), 'sitemapUrl' => $uri]);
    }
}