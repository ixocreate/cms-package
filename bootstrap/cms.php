<?php
declare(strict_types=1);
namespace Ixocreate\Package\Cms;

use Ixocreate\Package\Cms\Config\Configurator;
use Ixocreate\Package\Cms\Router\Replacement\LangReplacement;
use Ixocreate\Package\Cms\Router\Replacement\ParentReplacement;
use Ixocreate\Package\Cms\Router\Replacement\RegionReplacement;
use Ixocreate\Package\Cms\Router\Replacement\SlugReplacement;
use Ixocreate\Package\Cms\Router\Replacement\UriReplacement;
use Ixocreate\Package\Cms\Site\Tree\Search\ActiveSearch;
use Ixocreate\Package\Cms\Site\Tree\Search\CallableSearch;
use Ixocreate\Package\Cms\Site\Tree\Search\HandleSearch;
use Ixocreate\Package\Cms\Site\Tree\Search\MaxLevelSearch;
use Ixocreate\Package\Cms\Site\Tree\Search\MinLevelSearch;
use Ixocreate\Package\Cms\Site\Tree\Search\NavigationSearch;
use Ixocreate\Package\Cms\Site\Tree\Search\OnlineSearch;

/** @var Configurator $cms */
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
