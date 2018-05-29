<?php
namespace KiwiSuite\Cms\Message;

use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Cms\Handler\CreatePageHandler;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Message\MessageInterface;
use KiwiSuite\CommandBus\Message\MessageTrait;
use KiwiSuite\CommandBus\Message\Validation\Result;
use KiwiSuite\CommonTypes\Entity\UuidType;

final class CreatePage implements MessageInterface
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
    private $parentSitemapId;

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
            CreatePageHandler::class,
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

    public function parentSitemapId(): ?string
    {
        return $this->parentSitemapId;
    }

    public function pageType(): string
    {
        return $this->pageType;
    }

    public function createdBy(): UuidType
    {
        return $this->metadata()[User::class];
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

        if (!empty($this->data['parentSitemapId'])) {
            $sitemap = $this->sitemapRepository->find($this->data['parentSitemapId']);
            if (empty($sitemap)) {
                $result->addError("invalid_sitemap");
            }
        }

        //TODO check pageType
        //TODO check createdBy

        $this->name = $this->data['name'];
        $this->locale = $this->data['locale'];
        $this->parentSitemapId = $this->data['parentSitemapId'];
        $this->pageType = $this->data['pageType'];
        $this->createdBy = $this->data['createdBy'];
    }
}
