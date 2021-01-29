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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DetailAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * DetailAction constructor.
     * @param PageRepository $pageRepository
     * @param PageVersionRepository $pageVersionRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        PageRepository $pageRepository,
        PageVersionRepository $pageVersionRepository,
        UserRepository $userRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $versionId = $request->getAttribute('id');
        $pageId = $request->getAttribute('pageId');

        $page = $this->pageRepository->find($pageId);
        if ($page === null) {
            return new ApiErrorResponse('invalid_page_id');
        }

        /** @var PageVersion $pageVersion */
        $pageVersion = $this->pageVersionRepository->find($versionId);
        if (empty($pageVersion)) {
            return new ApiErrorResponse('invalid_page_version_id');
        }

        if ((string) $pageVersion->pageId() !== $pageId) {
            return new ApiErrorResponse('invalid_page_version_id');
        }

        $user = [
            'id' => null,
            'email' => null,
            'avatar' => null,
        ];

        /** @var User $adminUser */
        $adminUser = $this->userRepository->find((string)$pageVersion->createdBy());
        if ($adminUser !== null) {
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
