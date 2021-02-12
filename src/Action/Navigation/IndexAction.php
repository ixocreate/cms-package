<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Navigation;

use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Repository\NavigationRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexAction implements MiddlewareInterface
{
    /**
     * @var NavigationRepository
     */
    private $navigationRepository;

    public function __construct(NavigationRepository $navigationRepository)
    {
        $this->navigationRepository = $navigationRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $navigation = $this->navigationRepository->getNavigationForPage($request->getAttribute('id'));

        return new ApiSuccessResponse($navigation);
    }
}
