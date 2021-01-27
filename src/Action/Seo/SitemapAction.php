<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Seo;

use Ixocreate\Application\Uri\ApplicationUri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use Thepixeldeveloper\Sitemap\Sitemap;
use Thepixeldeveloper\Sitemap\SitemapIndex;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;

class SitemapAction implements MiddlewareInterface
{
    /**
     * @var ApplicationUri
     */
    private $projectUri;

    public function __construct(ApplicationUri $projectUri)
    {
        $this->projectUri = $projectUri;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();

        $requestPath = \mb_substr($uri->getPath(), 9);

        $response = null;
        if ($requestPath === 'sitemap.xml') {
            if (!\file_exists('data/sitemap/sitemap.json')) {
                return $handler->handle($request);
            }

            $sitemaps = \json_decode(\file_get_contents('data/sitemap/sitemap.json'), true);

            $sitemapIndex = new SitemapIndex();
            foreach ($sitemaps as $item) {
                $loc = $this->projectUri->getMainUri() . '/sitemap/' . $item['filename'];
                $sitemap = new Sitemap($loc);
                $sitemap->setLastMod(new \DateTime($item['createdAt']));

                $sitemapIndex->add($sitemap);
            }
            $driver = new XmlWriterDriver();
            $sitemapIndex->accept($driver);

            $response = new Response\XmlResponse($driver->output());
        } else {
            $filePath = 'data/sitemap/' . $requestPath;
            if (!\file_exists($filePath)) {
                return $handler->handle($request);
            }
            $response = (new Response())
                ->withHeader('Content-Type', 'text/xml; charset=utf-8')
                ->withHeader('Content-Length', (string)\filesize($filePath))
                ->withBody(new Stream($filePath));
        }

        return $response;
    }
}
