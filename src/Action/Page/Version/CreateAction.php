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
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
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
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * CreateAction constructor.
     * @param CommandBus $commandBus
     * @param PageRepository $pageRepository
     */
    public function __construct(CommandBus $commandBus, PageRepository $pageRepository)
    {
        $this->commandBus = $commandBus;
        $this->pageRepository = $pageRepository;
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

        /** @var Page $page */
        $page = $this->pageRepository->find($pageId);
        if ($page === null) {
            return new ApiErrorResponse('invalid_page_id');
        }

        $content = [];
        if (!empty($request->getParsedBody()['content']) && \is_array($request->getParsedBody()['content'])) {
            $content = $request->getParsedBody()['content'];
        }

        $result = $this->commandBus->command(CreateVersionCommand::class, [
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
