<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Test\Cms;

use Ixocreate\Cms\Block\BlockBootstrapItem;
use Ixocreate\Cms\CmsBootstrapItem;
use Ixocreate\Cms\Package;
use Ixocreate\Cms\PageType\PageTypeBootstrapItem;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    /**
     * @covers \Ixocreate\Cms\Package
     */
    public function testPackage()
    {
        $package = new Package();

        $this->assertSame([
            PageTypeBootstrapItem::class,
            BlockBootstrapItem::class,
            CmsBootstrapItem::class,
        ], $package->getBootstrapItems());

        $this->assertDirectoryExists($package->getBootstrapDirectory());
        $this->assertEmpty($package->getDependencies());
    }
}
