<?php
namespace KiwiSuite\Cms\Message;

use KiwiSuite\Cms\Handler\CreateSitemapHandler;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Message\MessageInterface;
use KiwiSuite\CommandBus\Message\MessageTrait;
use KiwiSuite\CommandBus\Message\Validation\Result;

final class CreateSitemap implements MessageInterface
{
    use MessageTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $parentId;

    /**
     * @var string
     */
    private $createdBy;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var string
     */
    private $pageType;

    /**
     * CreateSitemap constructor.
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(SitemapRepository $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @return array
     */
    public function handlers(): array
    {
        return [
            CreateSitemapHandler::class,
        ];
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function parentId(): ?string
    {
        return $this->parentId;
    }

    public function pageType(): string
    {
        return $this->pageType;
    }

    public function createdBy(): string
    {
        return $this->createdBy;
    }

    /**
     * @param Result $result
     */
    protected function doValidate(Result $result): void
    {
        if (empty($this->data['name'])) {
            $result->addError("invalid_name");
        }
        if (empty($this->data['locale'])) {
            $result->addError("invalid_locale");
        }

        if (!empty($this->data['parentId'])) {
            $sitemap = $this->sitemapRepository->find($this->data['parentId']);
            if (empty($sitemap)) {
                $result->addError("invalid_sitemap");
            }
        }

        //TODO check pageType
        //TODO check createdBy

        $this->name = $this->data['name'];
        $this->locale = $this->data['locale'];
        $this->parentId = $this->data['parentId'];
        $this->pageType = $this->data['pageType'];
        $this->createdBy = $this->data['createdBy'];
    }
}
