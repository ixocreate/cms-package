<?php

namespace KiwiSuite\Cms\Action\Page;


use Cocur\Slugify\Slugify;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * UpdateAction constructor.
     * @param PageRepository $pageRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return new ApiErrorResponse("invalid_data", [], 400);
        }

        /** @var Page $page */
        $page = $this->pageRepository->find($request->getAttribute("id"));
        if (empty($page)) {
            return new ApiErrorResponse("invalid_page");
        }

        $updated = false;
        if (!empty($data['name'])) {
            $updated = true;
            $page = $page->with("name", $data['name']);
        }

        if (!empty($data['publishedFrom'])) {
            $updated = true;
            $page = $page->with("publishedFrom", $data['publishedFrom']);
        }

        if (!empty($data['publishedUntil'])) {
            $updated = true;
            $page = $page->with("publishedUntil", $data['publishedUntil']);
        }

        if (!empty($data['status']) && in_array($data['status'], ['offline', 'online'])) {
            $updated = true;
            $page = $page->with("status", $data['status']);
        }

        if (!empty($data['slug'])) {
            $updated = true;
            $page = $page->with("slug", (new Slugify())->slugify($data['slug']));
        }

        if ($updated === true) {
            $page = $page->with('updatedAt', new \DateTime());
            $this->pageRepository->save($page);
        }

        return new ApiSuccessResponse();
    }
}
