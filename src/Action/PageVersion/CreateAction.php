<?php

namespace KiwiSuite\Cms\Action\PageVersion;


use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Message\PageVersion\CreatePageVersion;
use KiwiSuite\Cms\Resource\PageVersionResource;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\CommandBus\Message\MessageSubManager;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Resource\ResourceInterface;
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
    /**
     * @var PageVersionResource
     */
    private $pageVersionResource;

    public function __construct(MessageSubManager $messageSubManager, CommandBus $commandBus, PageVersionResource $pageVersionResource)
    {
        $this->messageSubManager = $messageSubManager;
        $this->commandBus = $commandBus;
        $this->pageVersionResource = $pageVersionResource;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        $body = $request->getParsedBody();
        if (empty($body)) {
            $body = [];
        }

        /** @var CreatePageVersion $message */
        $message = $this->messageSubManager->get(CreatePageVersion::class);

        $metadata = $routeResult->getMatchedParams();
        if (empty($metadata)) {
            $metadata = [];
        }
        $metadata[User::class] = $request->getAttribute(User::class, null)->id();
        $metadata[ResourceInterface::class] = \get_class($this->pageVersionResource);

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