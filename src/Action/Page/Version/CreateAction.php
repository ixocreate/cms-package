<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Action\Page\Version;

use Ixocreate\Package\Admin\Entity\User;
use Ixocreate\Package\Admin\Response\ApiErrorResponse;
use Ixocreate\Package\Admin\Response\ApiSuccessResponse;
use Ixocreate\Package\Cms\Command\Page\CreateVersionCommand;
use Ixocreate\Package\Cms\Site\Admin\Builder;
use Ixocreate\Package\Cms\Site\Admin\Item;
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
     * @var Builder
     */
    private $builder;

    /**
     * DetailAction constructor.
     * @param CommandBus $commandBus
     * @param Builder $builder
     */
    public function __construct(CommandBus $commandBus, Builder $builder)
    {
        $this->commandBus = $commandBus;
        $this->builder = $builder;
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
        /** @var Item $item */
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
