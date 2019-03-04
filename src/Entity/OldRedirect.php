<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Entity;

use Doctrine\DBAL\Types\Type;
use Ixocreate\CommonTypes\Entity\DateTimeType;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Contract\Entity\DatabaseEntityInterface;
use Ixocreate\Entity\Entity\Definition;
use Ixocreate\Entity\Entity\DefinitionCollection;
use Ixocreate\Entity\Entity\EntityInterface;
use Ixocreate\Entity\Entity\EntityTrait;
use Ixocreate\CommonTypes\Entity\UuidType;

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
            new Definition('oldUrl', Type::STRING, false, true),
            new Definition('pageId', UuidType::serviceName(), false, true),
            new Definition('createdAt', DateTimeType::serviceName(), false, true),
        ]);
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('cms_redirect_page');

        $builder->createField('oldUrl', Type::STRING)->makePrimaryKey()->build();
        $builder->createField('pageId', UuidType::serviceName())->nullable(false)->build();
        $builder->createField('createdAt', DateTimeType::serviceName())->nullable(false)->build();
    }
}
