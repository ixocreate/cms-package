<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page\Version;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Repository\UserRepository;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Entity\EntityCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * DetailAction constructor.
     * @param PageVersionRepository $pageVersionRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        PageVersionRepository $pageVersionRepository,
        UserRepository $userRepository,
        PageRepository $pageRepository
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->userRepository = $userRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $versionId = $request->getAttribute("id");
        $pageId = $request->getAttribute("pageId");
        $page = $this->pageRepository->find($pageId);


        if (empty($page)) {
            return new ApiErrorResponse("invalid_page_id");
        }

        $userResult = $this->userRepository->findAll();
        $userCollection = new EntityCollection($userResult, 'id');

        /** @var PageVersion $pageVersion */
        $pageVersion = $this->pageVersionRepository->find($versionId);
        if (empty($item)) {
            return new ApiErrorResponse("invalid_page_version_id");
        }

        if ((string) $pageVersion->pageId() !== $pageId) {
            return new ApiErrorResponse("invalid_page_version_id");
        }

        $user = [
            'id' => null,
            'email' => null,
            'avatar' => null,
        ];

        if ($userCollection->has((string) $pageVersion->createdBy())) {
            /** @var User $adminUser */
            $adminUser = $userCollection->get((string) $pageVersion->createdBy());
            $user['id'] = (string) $adminUser->id();
            $user['email'] = (string) $adminUser->email();
            $user['avatar'] = (string) $adminUser->avatar();
        }

        $content = [];
        if (!empty($pageVersion->content()->value())) {
            $content = $pageVersion->content()->value();
        }

        $result = [
            'id' => $pageVersion->id(),
            'approvedAt' => $pageVersion->approvedAt(),
            'createdAt' => $pageVersion->createdAt(),
            'content' => $content,
            'userId' => $user['id'],
            'userEmail' => $user['email'],
            'userAvatar' => $user['avatar'],
        ];

        return new ApiSuccessResponse($result);
    }
}
