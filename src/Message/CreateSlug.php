<?php
namespace KiwiSuite\Cms\Message;

use KiwiSuite\Cms\Handler\SlugHandler;
use KiwiSuite\CommandBus\Message\MessageInterface;
use KiwiSuite\CommandBus\Message\MessageTrait;
use KiwiSuite\CommandBus\Message\Validation\Result;
use KiwiSuite\CommonTypes\Entity\UuidType;
use KiwiSuite\Entity\Type\Type;

final class CreateSlug implements MessageInterface
{
    use MessageTrait;

    /**
     * @var string
     */
    private $pageId;


    /**
     * @return array
     */
    public function handlers(): array
    {
        return [
            SlugHandler::class,
        ];
    }

    /**
     * @return UuidType
     */
    public function pageId(): UuidType
    {
        return Type::create($this->pageId, UuidType::class);
    }

    /**
     * @param Result $result
     */
    protected function doValidate(Result $result): void
    {
        $this->pageId = $this->data['pageId'];
    }
}
