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
use Ixocreate\Entity\DatabaseEntityInterface;
use Ixocreate\Type\TypeInterface;
use Ixocreate\Database\Tree\NodeInterface;
use Ixocreate\Entity\Definition;
use Ixocreate\Entity\DefinitionCollection;
use Ixocreate\Entity\EntityTrait;
use Ixocreate\Type\Entity\UuidType;

final class Sitemap implements NodeInterface, DatabaseEntityInterface
{
    use EntityTrait;

    private $id;

    private $parentId;

    private $nestedLeft;

    private $nestedRight;

    private $pageType;

    private $handle;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function parentId(): ?UuidType
    {
        return $this->parentId;
    }

    public function nestedLeft(): ?int
    {
        return $this->nestedLeft;
    }

    public function nestedRight(): ?int
    {
        return $this->nestedRight;
    }

    public function pageType(): string
    {
        return $this->pageType;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('parentId', UuidType::class, true, true),
            new Definition('nestedLeft', TypeInterface::TYPE_INT, true, true),
            new Definition('nestedRight', TypeInterface::TYPE_INT, true, true),
            new Definition('pageType', TypeInterface::TYPE_STRING, false, true),
            new Definition('handle', TypeInterface::TYPE_STRING, true, true),
        ]);
    }

    public function right(): ?int
    {
        return $this->nestedRight();
    }

    public function left(): ?int
    {
        return $this->nestedLeft();
    }

    public function leftParameterName(): string
    {
        return 'nestedLeft';
    }

    public function rightParameterName(): string
    {
        return 'nestedRight';
    }

    public function parentIdParameterName(): string
    {
        return 'parentId';
    }

    public function idName(): string
    {
        return 'id';
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('cms_sitemap');

        $builder->createField('id', UuidType::serviceName())->makePrimaryKey()->build();
        $builder->createField('nestedLeft', Type::INTEGER)->nullable(true)->build();
        $builder->createField('nestedRight', Type::INTEGER)->nullable(true)->build();
        $builder->createField('parentId', UuidType::class)->nullable(true)->build();
        $builder->createField('pageType', Type::STRING)->nullable(false)->build();
        $builder->createField('handle', Type::STRING)->nullable(true)->build();
    }
}
