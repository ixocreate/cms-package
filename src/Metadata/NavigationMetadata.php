<?php

/**
 * kiwi-suite/cms (https://github.com/kiwi-suite/cms)
 *
 * @package kiwi-suite/cms
 * @see https://github.com/kiwi-suite/cms
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Metadata;

use Doctrine\DBAL\Types\Type;
use Ixocreate\Database\ORM\Metadata\AbstractMetadata;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;
use Ixocreate\CommonTypes\Entity\UuidType;

final class NavigationMetadata extends AbstractMetadata
{

    protected function buildMetadata(): void
    {
        $builder = $this->getBuilder();
        $builder->setTable('cms_navigation');

        $this->setFieldBuilder('id',
            $builder->createField('id', UuidType::class)
                ->makePrimaryKey()
        )->build();

        $this->setFieldBuilder('pageId',
            $builder->createField('pageId', UuidType::class)
        )->build();

        $this->setFieldBuilder('navigation',
            $builder->createField('navigation', Type::STRING)
        )->build();
    }
    
    public function id(): FieldBuilder
    {
        return $this->getField('id');
    }

    public function pageId(): FieldBuilder
    {
        return $this->getField('pageId');
    }

    public function content(): FieldBuilder
    {
        return $this->getField('navigation');
    }
}

