<?php

namespace KiwiSuite\Cms\Action\Page;


use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Response\ApiDetailResponse;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Message\CreatePage;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\CommandBus\Message\MessageSubManager;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Resource\ResourceInterface;
use KiwiSuite\Schema\Schema;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

class CreateAction implements MiddlewareInterface
{


    /**
     * @var MessageSubManager
     */
    private $messageSubManager;
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(MessageSubManager $messageSubManager, CommandBus $commandBus)
    {
        $this->messageSubManager = $messageSubManager;
        $this->commandBus = $commandBus;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        /** @var AdminAwareInterface $resource */
        $resource = $request->getAttribute(ResourceInterface::class);

        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = [];
        }

        /** @var CreatePage $message */
        $message = $this->messageSubManager->get(CreatePage::class);

        $metadata = $routeResult->getMatchedParams();
        if (empty($metadata)) {
            $metadata = [];
        }
        $metadata[User::class] = $request->getAttribute(User::class, null)->id();
        $metadata[ResourceInterface::class] = \get_class($resource);

        $message = $message->inject($body, $metadata);
        $result = $message->validate();
        if (!$result->isSuccessful()) {
            return new ApiErrorResponse('invalid.input', $result->getErrors());
        }

        $this->commandBus->handle($message);
        return new ApiSuccessResponse([
            'id' => (string) $message->uuid()
        ]);
    }
}
