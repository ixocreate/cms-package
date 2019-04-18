<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Template;

use Ixocreate\Cms\Package\Site\Tree\Container;
use Ixocreate\Template\Package\ExtensionInterface;

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
