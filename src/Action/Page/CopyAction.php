<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Action\Page;

use Ixocreate\Admin\Package\Entity\User;
use Ixocreate\Admin\Package\Response\ApiErrorResponse;
use Ixocreate\Admin\Package\Response\ApiSuccessResponse;
use Ixocreate\Cms\Package\Command\Page\CopyPageCommand;
use Ixocreate\CommandBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CopyAction implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * CopyPageAction constructor.
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!\is_array($data)) {
            return new ApiErrorResponse("invalid_data", [], 400);
        }

        $data['createdBy'] = (string) $request->getAttribute(User::class)->id();

        $result = $this->commandBus->command(CopyPageCommand::class, $data);
        if ($result->isSuccessful()) {
            return new ApiSuccessResponse([
                'toPageId' => (string) $result->command()->toPage()->id(),
                'toSitemapId' => (string) $result->command()->toSitemap()->id(),
            ]);
        }

        return new ApiErrorResponse('execution_error', $result->messages());
    }
}
