<?php
namespace KiwiSuite\Cms\Request;

use KiwiSuite\ApplicationHttp\Request\AbstractRequestWrapper;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;

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
}
