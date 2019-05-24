<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\OldRedirect;
use Ixocreate\Cms\Repository\OldRedirectRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class CreateAliasPageAction implements MiddlewareInterface
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
        $slug = $request->getParsedBody();
        if (!Uuid::isValid($slug['urlId'])) {
            return new ApiErrorResponse("Invalid Uuid");
        }
        if (!\is_string($slug['url'])) {
            return new ApiErrorResponse("Invalid Id");
        }

        if ($this->oldRedirectRepository->findOneBy(['oldUrl' => $slug['url']])) {
            $pageVersion = $this->oldRedirectRepository->findOneBy(['oldUrl' => $slug['url']]);
            $this->oldRedirectRepository->remove($pageVersion);
        }

        $redirect = new OldRedirect([
            'oldUrl' => $slug['url'],
            'pageId' => $slug['urlId'],
            'createdAt' => new \DateTime(),
        ]);

        $this->oldRedirectRepository->save($redirect);

        return new ApiSuccessResponse();
    }
}
