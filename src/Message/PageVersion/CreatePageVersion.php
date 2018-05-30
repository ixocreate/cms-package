<?php
namespace KiwiSuite\Cms\Message\PageVersion;

use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Cms\Handler\PageVersion\CreatePageVersionHandler;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\CommandBus\Message\MessageInterface;
use KiwiSuite\CommandBus\Message\MessageTrait;
use KiwiSuite\CommandBus\Message\Validation\Result;
use KiwiSuite\CommonTypes\Entity\UuidType;
use KiwiSuite\Entity\Type\Type;

final class CreatePageVersion implements MessageInterface
{
    use MessageTrait;

    /**
     * @var array
     */
    private $content;

    /**
     * @var string
     */
    private $pageId;

    /**
     * @var PageRepository
     */
    private $pageRepository;


    /**
     * CreateSitemap constructor.
     * @param PageRepository $pageRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return array
     */
    public function handlers(): array
    {
        return [
            CreatePageVersionHandler::class,
        ];
    }

    /**
     * @return array
     */
    public function content(): array
    {
        return $this->content;
    }

    public function pageId(): UuidType
    {
        return Type::create($this->pageId, UuidType::class);
    }

    public function createdBy(): UuidType
    {
        return Type::create($this->metadata()[User::class], UuidType::class);
    }

    /**
     * @param Result $result
     */
    protected function doValidate(Result $result): void
    {
        if (empty($this->metadata()['id'])){
            $result->addError("invalid_page");
            return;
        }
        $page = $this->pageRepository->find($this->metadata()['id']);
        if (empty($page)) {
            $result->addError("invalid_page");
            return;
        }

        $content = [];
        if (!empty($this->data()['content']) && is_array($this->data()['content'])) {
            $content = $this->data()['content'];
        }

        $this->pageId = $this->metadata()['id'];
        $this->content = $content;
    }
}
