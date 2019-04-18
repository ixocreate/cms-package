<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Action\Page;

use Ixocreate\Package\Admin\Response\ApiErrorResponse;
use Ixocreate\Package\Admin\Response\ApiSuccessResponse;
use Ixocreate\Package\Cms\Repository\OldRedirectRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class ShowAliasPageAction implements MiddlewareInterface
{
    /**
     * @var OldRedirectRepository
     */
    private $oldRedirectRepository;

    public function __construct(OldRedirectRepository $oldRedirectRepository)
    {
        $this->oldRedirectRepository = $oldRedirectRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!Uuid::isValid($request->getAttribute("id"))) {
            return new ApiErrorResponse("Invalid Uuid");
        }
        $id = $request->getAttribute("id");

        $pages = $this->oldRedirectRepository->findBy(['pageId' => $id]);

        $result = $pages;

        return new ApiSuccessResponse($result);
    }
}
