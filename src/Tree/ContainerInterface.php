<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Collection\CollectionInterface;

interface ContainerInterface extends \RecursiveIterator, \Countable
{
    /**
     * @param array $ids
     * @return ContainerInterface
     */
    public function only(array $ids): ContainerInterface;

    /**
     * @param string|callable $searchable
     * @param array $params
     * @return ContainerInterface
     */
    public function filter($searchable, array $params = []): ContainerInterface;

    /**
     * @param string|callable $mutatable
     * @param array $params
     * @return ContainerInterface
     */
    public function map($mutatable, array $params = []): ContainerInterface;

    /**
     * @param string|callable $searchable
     * @param array $params
     * @return ContainerInterface
     */
    public function where($searchable, array $params = []): ContainerInterface;

    /**
     * @return CollectionInterface
     */
    public function flatten(): CollectionInterface;

    /**
     * @param string|callable $searchable
     * @param array $params
     * @return CollectionInterface
     */
    public function find($searchable, array $params = []): CollectionInterface;

    /**
     * @param string|callable $searchable
     * @param array $params
     * @return ItemInterface|null
     */
    public function findOne($searchable, array $params = []): ?ItemInterface;
}
