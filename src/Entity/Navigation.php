<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Database\DatabaseEntityInterface;
use Ixocreate\Entity\Definition;
use Ixocreate\Entity\DefinitionCollection;
use Ixocreate\Entity\EntityInterface;
use Ixocreate\Entity\EntityTrait;
use Ixocreate\Type\Entity\UuidType;
use Ixocreate\Type\TypeInterface;

final class Navigation implements EntityInterface, DatabaseEntityInterface
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

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('cms_navigation');

        $builder->createField('id', UuidType::serviceName())->makePrimaryKey()->build();
        $builder->createField('pageId', UuidType::serviceName())->nullable(false)->build();
        $builder->createField('navigation', Type::STRING)->nullable(false)->build();
    }
}
