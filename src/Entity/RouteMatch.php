<?php

declare(strict_types=1);

namespace Ixocreate\Cms\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Database\DatabaseEntityInterface;
use Ixocreate\Entity\Definition;
use Ixocreate\Entity\DefinitionCollection;
use Ixocreate\Entity\EntityInterface;
use Ixocreate\Entity\EntityTrait;
use Ixocreate\Schema\Type\TypeInterface;
use Ixocreate\Schema\Type\UuidType;

final class RouteMatch implements EntityInterface, DatabaseEntityInterface
{
    use EntityTrait;

    private $url;
    private $type;
    private $pageId;
    private $middleware;

    public function url(): string
    {
        return $this->url;
    }
    public function type(): string
    {
        return $this->type;
    }
    public function pageId(): UuidType
    {
        return $this->pageId;
    }
    public function middleware(): array
    {
        return $this->middleware;
    }

    public static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('url', TypeInterface::TYPE_STRING, false, true),
            new Definition('type', TypeInterface::TYPE_STRING, false, true),
            new Definition('pageId', UuidType::class, false, true),
            new Definition('middleware', TypeInterface::TYPE_ARRAY, false, true),
        ]);
    }

    public static function loadMetadata(ClassMetadataBuilder $builder)
    {
        $builder->setTable('cms_route_match');

        $builder->createField('url', 'string')->makePrimaryKey()->build();
        $builder->createField('type', 'string')->nullable(false)->build();
        $builder->createField('pageId', UuidType::serviceName())->nullable(false)->build();
        $builder->createField('middleware', Type::JSON)->build();
    }

}

