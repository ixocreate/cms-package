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
use Ixocreate\Type\Entity\DateTimeType;
use Ixocreate\Database\DatabaseEntityInterface;
use Ixocreate\Type\TypeInterface;
use Ixocreate\Entity\Definition;
use Ixocreate\Entity\DefinitionCollection;
use Ixocreate\Entity\EntityInterface;
use Ixocreate\Entity\EntityTrait;
use Ixocreate\Type\Entity\UuidType;

final class Page implements EntityInterface, DatabaseEntityInterface
{
    use EntityTrait;

    private $id;

    private $sitemapId;

    private $locale;

    private $name;

    private $slug;

    private $publishedFrom;

    private $publishedUntil;

    private $status;

    private $updatedAt;

    private $createdAt;

    private $releasedAt;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function sitemapId(): UuidType
    {
        return $this->sitemapId;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): ?string
    {
        return $this->slug;
    }

    public function publishedFrom(): ?DateTimeType
    {
        return $this->publishedFrom;
    }

    public function publishedUntil(): ?DateTimeType
    {
        return $this->publishedUntil;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function updatedAt(): DateTimeType
    {
        return $this->updatedAt;
    }

    public function releasedAt(): DateTimeType
    {
        return $this->releasedAt;
    }

    public function createdAt(): DateTimeType
    {
        return $this->createdAt;
    }

    public function isOnline(): bool
    {
        if ($this->status() === "offline") {
            return false;
        }

        if ($this->publishedFrom() instanceof DateTimeType && $this->publishedFrom()->value()->getTimestamp() > \time()) {
            return false;
        }

        if ($this->publishedUntil() instanceof DateTimeType && $this->publishedUntil()->value()->getTimestamp() < \time()) {
            return false;
        }

        return true;
    }

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('sitemapId', UuidType::class, false, true),
            new Definition('locale', TypeInterface::TYPE_STRING, false, true),
            new Definition('name', TypeInterface::TYPE_STRING, false, true),
            new Definition('slug', TypeInterface::TYPE_STRING, true, true),
            new Definition('publishedFrom', DateTimeType::class, true, true),
            new Definition('publishedUntil', DateTimeType::class, true, true),
            new Definition('status', TypeInterface::TYPE_STRING, false, true),
            new Definition('updatedAt', DateTimeType::class, false, true),
            new Definition('createdAt', DateTimeType::class, false, true),
            new Definition('releasedAt', DateTimeType::class, false, true),
        ]);
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('cms_page');

        $builder->createField('id', UuidType::serviceName())->makePrimaryKey()->build();
        $builder->createField('sitemapId', UuidType::serviceName())->nullable(false)->build();
        $builder->createField('locale', Type::STRING)->nullable(false)->build();
        $builder->createField('name', Type::STRING)->nullable(false)->build();
        $builder->createField('slug', Type::STRING)->nullable(true)->build();
        $builder->createField('publishedFrom', DateTimeType::serviceName())->nullable(true)->build();
        $builder->createField('publishedUntil', DateTimeType::serviceName())->nullable(true)->build();
        $builder->createField('status', Type::STRING)->nullable(false)->build();
        $builder->createField('updatedAt', DateTimeType::serviceName())->nullable(false)->build();
        $builder->createField('createdAt', DateTimeType::serviceName())->nullable(false)->build();
        $builder->createField('releasedAt', DateTimeType::serviceName())->nullable(false)->build();
    }
}
