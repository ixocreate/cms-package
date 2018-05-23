<?php
declare(strict_types=1);

namespace KiwiSuite\Cms;

use KiwiSuite\Admin\Resource\ResourceConfigurator;
use KiwiSuite\Cms\Resource\PageResource;

/** @var ResourceConfigurator $resource */
$resource->addResource(PageResource::class);
