<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Application\Publish\PublishConfigurator;

/** @var PublishConfigurator $publish */
$publish->addSource('migrations', __DIR__ . '/../resources/migrations');
