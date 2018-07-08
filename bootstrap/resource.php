<?php
declare(strict_types=1);

namespace KiwiSuite\Cms;

/** @var ResourceConfigurator $resource */
use KiwiSuite\Cms\Resource\PageResource;
use KiwiSuite\Cms\Resource\PageVersionResource;
use KiwiSuite\Resource\SubManager\ResourceConfigurator;

$resource->addResource(PageResource::class);
$resource->addResource(PageVersionResource::class);
