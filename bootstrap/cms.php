<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Cms\Router\Replacement\LangReplacement;
use Ixocreate\Cms\Router\Replacement\ParentReplacement;
use Ixocreate\Cms\Router\Replacement\RegionReplacement;
use Ixocreate\Cms\Router\Replacement\SlugReplacement;
use Ixocreate\Cms\Router\Replacement\UriReplacement;
use Ixocreate\Cms\Site\Tree\Search\ActiveSearch;
use Ixocreate\Cms\Site\Tree\Search\CallableSearch;
use Ixocreate\Cms\Site\Tree\Search\HandleSearch;
use Ixocreate\Cms\Site\Tree\Search\MaxLevelSearch;
use Ixocreate\Cms\Site\Tree\Search\MinLevelSearch;
use Ixocreate\Cms\Site\Tree\Search\NavigationSearch;
use Ixocreate\Cms\Site\Tree\Search\OnlineSearch;

/** @var CmsConfigurator $cms */
$cms->addTreeSearchable(ActiveSearch::class);
$cms->addTreeSearchable(CallableSearch::class);
$cms->addTreeSearchable(HandleSearch::class);
$cms->addTreeSearchable(MaxLevelSearch::class);
$cms->addTreeSearchable(MinLevelSearch::class);
$cms->addTreeSearchable(NavigationSearch::class);
$cms->addTreeSearchable(OnlineSearch::class);

$cms->addRoutingReplacement(LangReplacement::class);
$cms->addRoutingReplacement(RegionReplacement::class);
$cms->addRoutingReplacement(ParentReplacement::class);
$cms->addRoutingReplacement(SlugReplacement::class);
$cms->addRoutingReplacement(UriReplacement::class);
