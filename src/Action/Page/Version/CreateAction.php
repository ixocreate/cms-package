<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page\Version;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Command\Page\CreateVersionCommand;
use Ixocreate\Cms\Site\Admin\AdminContainer;
use Ixocreate\Cms\Site\Admin\AdminItem;
use Ixocreate\CommandBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CreateAction implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AdminContainer
     */
    private $adminContainer;

    /**
     * CreateAction constructor.
     * @param CommandBus $commandBus
     * @param AdminContainer $adminContainer
     */
    public function __construct(CommandBus $commandBus, AdminContainer $adminContainer)
    {
        $this->commandBus = $commandBus;
        $this->adminContainer = $adminContainer;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @throws \Exception
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageId = $request->getAttribute('id');
        /** @var AdminItem $item */
        $item = $this->adminContainer->findOneBy(function (AdminItem $item) use ($pageId) {
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

        $page = null;
        foreach ($item->pages() as $pageItem) {
            if ((string) $pageItem['page']->id() === $pageId) {
                $page = $pageItem['page'];
            }
        }

        $content = [];
        if (!empty($request->getParsedBody()['content']) && \is_array($request->getParsedBody()['content'])) {
            $content = $request->getParsedBody()['content'];
        }

        $result = $this->commandBus->command(CreateVersionCommand::class, [
            'pageType' => $item->pageType()::serviceName(),
            'pageId' => (string) $page->id(),
            'createdBy' => $request->getAttribute(User::class, null)->id(),
            'content' => $content,
            'approve' => true,
        ]);

        if ($result->isSuccessful()) {
            return new ApiSuccessResponse((string) $result->command()->uuid());
        }

        return new ApiErrorResponse('execution_error', $result->messages());
    }
}
