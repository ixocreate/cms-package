<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Tree;

interface ContainerInterface extends \RecursiveIterator, \Countable
{
    /**
     * @param string|callable $filter
     * @return ContainerInterface
     */
    public function filter($filter, array $params = []): self;

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMaxLevel(int $level): self;

    /**
     * @param string $navigation
     * @return ContainerInterface
     */
    public function withNavigation(string $navigation): self;

    /**
     * @param string|callable $filter
     * @return ContainerInterface
     */
    public function where($filter, array $params = []): self;

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMinLevel(int $level): self;

    /**
     * @return ContainerInterface
     */
    public function flatten(): self;

    /**
     * @param string|callable $filter
     * @return Item|null
     */
    public function find($filter, array $params = []): ?Item;

    /**
     * @param string $handle
     * @return Item|null
     */
    public function findByHandle(string $handle): ?Item;

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function sort(callable $callable): self;
}
