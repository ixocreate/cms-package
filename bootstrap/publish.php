<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

/** @var PublishConfigurator $publish */
use Ixocreate\Application\Publish\PublishConfigurator;

$publish->add('migrations', __DIR__ . '/../resources/migrations');
