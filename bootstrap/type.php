<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Cms\Schema\Type\BlockContainerType;
use Ixocreate\Cms\Schema\Type\BlockType;
use Ixocreate\Schema\Type\TypeConfigurator;

/** @var TypeConfigurator $type */
$type->addType(BlockContainerType::class);
$type->addType(BlockType::class);
