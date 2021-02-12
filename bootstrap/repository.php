<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Ixocreate\Database\Repository\RepositoryConfigurator;

/** @var RepositoryConfigurator $repository */
$repository->addRepository(NavigationRepository::class);
$repository->addRepository(OldRedirectRepository::class);
$repository->addRepository(PageRepository::class);
$repository->addRepository(PageVersionRepository::class);
$repository->addRepository(RouteMatchRepository::class);
$repository->addRepository(SitemapRepository::class);
