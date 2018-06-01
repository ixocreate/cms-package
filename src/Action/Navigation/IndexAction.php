<?php

namespace KiwiSuite\Cms\Action\Navigation;


use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Config\Config;
use KiwiSuite\Cms\Entity\Navigation;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\NavigationRepository;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexAction implements MiddlewareInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var NavigationRepository
     */
    private $navigationRepository;

    public function __construct(Config $config, NavigationRepository $navigationRepository)
    {
        $this->config = $config;
        $this->navigationRepository = $navigationRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $navigation = $this->config->navigation();
        $navigation = array_map(function ($value){
            $value['active'] = false;
        }, $navigation);

        $result = $this->navigationRepository->findBy(['pageId' => $request->getAttribute("id")]);
        /** @var Navigation $item */
        foreach ($result as $item) {
            foreach ($navigation as &$navigationItem) {
                if ($navigationItem['name'] === $item->navigation()) {
                    $navigationItem['active'] =  true;
                    break;
                }
            }
        }

        return new ApiSuccessResponse($navigation);
    }
}
