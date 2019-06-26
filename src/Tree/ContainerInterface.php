<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Countable;
use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Collection\CollectionInterface;
use RecursiveIterator;

interface ContainerInterface extends Countable, RecursiveIterator
{
    public function factory(): FactoryInterface;

    public function structure(): Structure;

    public function find($filter, array $params = []): ?ItemInterface;

    public function where($filter, array $params = []): ContainerInterface;

    public function flatten(): CollectionInterface;

    public function search($filter, array $params = []): CollectionInterface;

    public function filter($filter, array $params = []): ContainerInterface;
}
