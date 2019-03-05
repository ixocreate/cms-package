<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Site\Tree;

interface ContainerInterface extends \RecursiveIterator, \Countable
{
    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function filter(callable $callable): self;

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
     * @param callable $callable
     * @return ContainerInterface
     */
    public function where(callable $callable): self;

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
     * @param callable $callable
     * @return Item|null
     */
    public function find(callable $callable): ?Item;

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
