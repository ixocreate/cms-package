<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Entity;

use Ixocreate\Entity\Entity\Definition;
use Ixocreate\Entity\Entity\DefinitionCollection;
use Ixocreate\Entity\Entity\EntityInterface;
use Ixocreate\Entity\Entity\EntityTrait;
use Ixocreate\CommonTypes\Entity\UuidType;
use Ixocreate\Entity\Type\TypeInterface;

final class Navigation implements EntityInterface
{
    use EntityTrait;

    private $id;

    private $pageId;

    private $navigation;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function pageId(): UuidType
    {
        return $this->pageId;
    }

    public function navigation()
    {
        return $this->navigation;
    }

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('pageId', UuidType::class, false, true),
            new Definition('navigation', TypeInterface::TYPE_STRING, false, true),
        ]);
    }
}
