<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin;

interface AdminContainerInterface extends \RecursiveIterator, \Countable
{
    /**
     * @param string|callable $filter
     * @return AdminContainerInterface
     */
    public function filter($filter, array $params = []): self;

    /**
     * @param callable $map
     * @param array $params
     * @return AdminContainerInterface
     */
    public function map(callable $map, array $params = []): self;

    /**
     * @param int $level
     * @return AdminContainerInterface
     */
    public function withMaxLevel(int $level): self;

    /**
     * @param string $navigation
     * @return AdminContainerInterface
     */
    public function withNavigation(string $navigation): self;

    /**
     * @param string|callable $filter
     * @return AdminContainerInterface
     */
    public function where($filter, array $params = []): self;

    /**
     * @param int $level
     * @return AdminContainerInterface
     */
    public function withMinLevel(int $level): self;

    /**
     * @return AdminContainerInterface
     */
    public function flatten(): self;

    /**
     * @param string|callable $filter
     * @return AdminItem|null
     */
    public function find($filter, array $params = []): ?AdminItem;

    /**
     * @param string $handle
     * @return AdminItem|null
     */
    public function findByHandle(string $handle): ?AdminItem;

    /**
     * @param callable $callable
     * @return AdminContainerInterface
     */
    public function sort(callable $callable): self;

    /**
     * @param int $limit
     * @param int $offset
     * @return AdminContainerInterface
     */
    public function paginate(int $limit, int $offset = 0): self;
}
