<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Template;

use Ixocreate\Cms\Site\Tree\Container;
use Ixocreate\Template\Extension\ExtensionInterface;

final class TreeExtension implements ExtensionInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * TreeExtension constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return "tree";
    }

    public function __invoke()
    {
        return $this->container;
    }
}
