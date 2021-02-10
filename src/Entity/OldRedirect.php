<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Database\DatabaseEntityInterface;
use Ixocreate\Entity\Definition;
use Ixocreate\Entity\DefinitionCollection;
use Ixocreate\Entity\EntityInterface;
use Ixocreate\Entity\EntityTrait;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\TypeInterface;
use Ixocreate\Schema\Type\UuidType;

final class OldRedirect implements EntityInterface, DatabaseEntityInterface
{
    use EntityTrait;

    private $oldUrl;

    private $pageId;

    private $createdAt;

    public function oldUrl(): string
    {
        return $this->oldUrl;
    }

    public function pageId(): UuidType
    {
        return $this->pageId;
    }

    public function createdAt()
    {
        return $this->createdAt;
    }

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('oldUrl', TypeInterface::TYPE_STRING, false, true),
            new Definition('pageId', UuidType::serviceName(), false, true),
            new Definition('createdAt', DateTimeType::serviceName(), false, true),
        ]);
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('cms_redirect_page');

        $builder->createField('oldUrl', Types::STRING)->makePrimaryKey()->build();
        $builder->createField('pageId', UuidType::serviceName())->nullable(false)->build();
        $builder->createField('createdAt', DateTimeType::serviceName())->nullable(false)->build();
    }
}
