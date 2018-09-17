<?php
namespace KiwiSuite\Cms\Action\Page\Version;

use Doctrine\Common\Collections\Criteria;
use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Repository\UserRepository;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Site\Admin\Builder;
use KiwiSuite\Cms\Site\Admin\Item;
use KiwiSuite\Entity\Entity\EntityCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class IndexAction implements MiddlewareInterface
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

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('pageId', $pageId));
        $criteria->orderBy(['createdAt' => 'DESC']);
        $criteria->setMaxResults(50);
        $pageVersionResult = $this->pageVersionRepository->matching($criteria);

        $result = [];

        /** @var PageVersion $pageVersion */
        foreach ($pageVersionResult as $pageVersion) {
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

            $result[] = [
                'id' => $pageVersion->id(),
                'approvedAt' => $pageVersion->approvedAt(),
                'createdAt' => $pageVersion->createdAt(),
                'userId' => $user['id'],
                'userEmail' => $user['email'],
                'userAvatar' => $user['avatar'],
            ];
        }

        return new ApiSuccessResponse($result);
    }
}
