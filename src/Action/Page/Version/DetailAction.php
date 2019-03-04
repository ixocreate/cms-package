<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page\Version;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Repository\UserRepository;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Site\Admin\Builder;
use Ixocreate\Cms\Site\Admin\Item;
use Ixocreate\Entity\Entity\EntityCollection;
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
     * @var Builder
     */
    private $builder;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * DetailAction constructor.
     * @param Builder $builder
     * @param PageVersionRepository $pageVersionRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        Builder $builder,
        PageVersionRepository $pageVersionRepository,
        UserRepository $userRepository
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->builder = $builder;
        $this->userRepository = $userRepository;
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
        $item = $this->builder->build()->findOneBy(function (Item $item) use ($pageId) {
            $pages = $item->pages();
            foreach ($pages as $pageItem) {
                if ((string) $pageItem['page']->id() === $pageId) {
                    return true;
                }
            }

            return false;
        });

        if (empty($item)) {
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
