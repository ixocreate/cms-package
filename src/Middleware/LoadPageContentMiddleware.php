<?php
namespace KiwiSuite\Cms\Middleware;

use Doctrine\Common\Collections\Criteria;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LoadPageContentMiddleware implements MiddlewareInterface
{

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    public function __construct(PageVersionRepository $pageVersionRepository)
    {
        $this->pageVersionRepository = $pageVersionRepository;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Page $page */
        $page = $request->getPage();

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('pageId', $page->id()));
        $criteria->andWhere(Criteria::expr()->neq('approvedAt', null));
        $criteria->orderBy(['approvedAt' => 'DESC']);
        $criteria->setMaxResults(1);

        $pageVersion = $this->pageVersionRepository->matching($criteria);
        /** @var PageVersion $pageVersion */
        $pageVersion = $pageVersion->current();

        $request = $request->withPageVersion($pageVersion);

        return $handler->handle($request);
    }
}
