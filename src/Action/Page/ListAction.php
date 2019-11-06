<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Site\Admin\AdminContainer;
use Ixocreate\Cms\Site\Admin\AdminItem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListAction implements MiddlewareInterface
{
    /**
     * @var AdminContainer
     */
    private $adminContainer;

    /**
     * ListAction constructor.
     *
     * @param AdminContainer $adminContainer
     */
    public function __construct(AdminContainer $adminContainer)
    {
        $this->adminContainer = $adminContainer;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!\array_key_exists('locale', $request->getQueryParams())) {
            return new ApiErrorResponse('invalid locale');
        }
        $locale = $request->getQueryParams()['locale'];
        $pageType = null;
        if (!empty($request->getQueryParams()['pageType'])) {
            $pageType = $request->getQueryParams()['pageType'];
        }

        $result = [];
        $iterator = new \RecursiveIteratorIterator($this->adminContainer, \RecursiveIteratorIterator::SELF_FIRST);
        $terminalIgnoreDepth = 0;
        $ignoringChildren = false;
        /** @var AdminItem $item */
        foreach ($iterator as $item) {

            /**
             * lift ignoring children flag as soon as we're out the children's depth again
             */
            if($ignoringChildren && $terminalIgnoreDepth === $iterator->getDepth()) {
                $ignoringChildren = false;
            }

            if($ignoringChildren) {
                continue;
            }

            /**
             * set flags to exclude children of terminal page types (flat lists)
             */
            if ($item->pageType() instanceof TerminalPageTypeInterface) {
                $terminalIgnoreDepth = $iterator->getDepth();
                $ignoringChildren = true;
            }

            /**
             * exclude pages that do not have the requested locale
             */
            if (!\array_key_exists($locale, $item->pages())) {
                continue;
            }

            /**
             * exclude pages that are not of the requested page type
             */
            if ($pageType !== null && $item->pageType()::serviceName() !== $pageType) {
                continue;
            }

            $result[] = [
                'id' => $item->pages()[$locale]['page']->id(),
                'name' => $this->receiveFullName($item, $locale),
                'pageType' => $item->pageType()::serviceName(),
                'terminal' => $item->pageType() instanceof TerminalPageTypeInterface,
            ];
        }

        return new ApiSuccessResponse($result);
    }

    /**
     * @param AdminItem $item
     * @param string $locale
     * @return string
     */
    private function receiveFullName(AdminItem $item, string $locale): string
    {
        $name = '';
        if (!empty($item->parent())) {
            $name .= $this->receiveFullName($item->parent(), $locale) . ' / ';
        }

        if (!\array_key_exists($locale, $item->pages())) {
            return ' --- ';
        }

        return $name . $item->pages()[$locale]['page']->name();
    }
}
