<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Countable;
use RecursiveIterator;

interface ContainerInterface extends Countable, RecursiveIterator
{
}
