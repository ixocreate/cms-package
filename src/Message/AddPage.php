<?php
namespace KiwiSuite\Cms\Message;

use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Cms\Handler\AddPageHandler;
use KiwiSuite\Cms\Handler\CreatePageHandler;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Message\MessageInterface;
use KiwiSuite\CommandBus\Message\MessageTrait;
use KiwiSuite\CommandBus\Message\Validation\Result;
use KiwiSuite\CommonTypes\Entity\UuidType;

final class AddPage implements MessageInterface
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
    private $sitemapId;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

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
            AddPageHandler::class,
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

    public function sitemapId(): ?string
    {
        return $this->sitemapId;
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

        if (empty($this->data['sitemapId'])) {
            $result->addError("invalid_sitemap");
        } else {
            $sitemap = $this->sitemapRepository->find($this->data['sitemapId']);
            if (empty($sitemap)) {
                $result->addError("invalid_sitemap");
            } else {
                $this->sitemapId = $this->data['sitemapId'];
            }
        }

        $this->name = $this->data['name'];
        $this->locale = $this->data['locale'];
    }
}
