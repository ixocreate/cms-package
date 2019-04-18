<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Request;

use Ixocreate\Application\Http\Request\AbstractRequestWrapper;
use Ixocreate\Package\Cms\Entity\Page;
use Ixocreate\Package\Cms\Entity\PageVersion;
use Ixocreate\Package\Cms\Entity\Sitemap;
use Ixocreate\Package\Cms\PageType\PageTypeInterface;

final class CmsRequest extends AbstractRequestWrapper
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var Sitemap
     */
    private $sitemap;

    /**
     * @var PageVersion
     */
    private $pageVersion;

    /**
     * @var PageTypeInterface
     */
    private $pageType;

    private $templateAttributes = [];

    private $globalTemplateAttributes = [];

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function withPage(Page $page): CmsRequest
    {
        $request = clone $this;
        $request->page = $page;

        return $request;
    }

    public function getSitemap(): ?Sitemap
    {
        return $this->sitemap;
    }

    public function withSitemap(Sitemap $sitemap): CmsRequest
    {
        $request = clone $this;
        $request->sitemap = $sitemap;

        return $request;
    }

    public function getPageVersion(): ?PageVersion
    {
        return $this->pageVersion;
    }

    public function withPageVersion(PageVersion $pageVersion): CmsRequest
    {
        $request = clone $this;
        $request->pageVersion = $pageVersion;

        return $request;
    }

    public function getPageType(): ?PageTypeInterface
    {
        return $this->pageType;
    }

    public function withPageType(PageTypeInterface $pageType): CmsRequest
    {
        $request = clone $this;
        $request->pageType = $pageType;

        return $request;
    }

    public function getTemplateAttributes(): array
    {
        return $this->templateAttributes;
    }

    public function withAddedTemplateAttribute(string $name, $value): CmsRequest
    {
        $request = clone $this;
        $request->templateAttributes[$name] = $value;

        return $request;
    }

    public function getGlobalTemplateAttributes(): array
    {
        return $this->globalTemplateAttributes;
    }

    public function withAddedGlobalTemplateAttribute(string $name, $value): CmsRequest
    {
        $request = clone $this;
        $request->globalTemplateAttributes[$name] = $value;

        return $request;
    }
}
