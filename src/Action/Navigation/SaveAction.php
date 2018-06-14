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
use Ramsey\Uuid\Uuid;

class SaveAction implements MiddlewareInterface
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
        $queryBuilder = $this->navigationRepository->createQueryBuilder();
        $queryBuilder->delete(Navigation::class, "nav")
            ->where("nav.pageId = :pageId")
            ->setParameter("pageId", $request->getAttribute("id"));
        $queryBuilder->getQuery()->execute();
        
        $parsedBody = $request->getParsedBody();

        $navigation = array_map(function ($value) use($parsedBody){
            $value['active'] = (in_array($value['name'], $parsedBody));

            return $value;
        }, $this->config->navigation());


        foreach ($navigation as $nav) {
            if ($nav['active'] === false) {
                continue;
            }
            $navigationEntity = new Navigation([
                'id' => Uuid::uuid4()->toString(),
                'pageId' => $request->getAttribute("id"),
                'navigation' => $nav['name'],
            ]);

            $this->navigationRepository->save($navigationEntity);
        }

        return new ApiSuccessResponse();
    }
}
