<?php

namespace Azure\Storage\Library\Defaults\Http\Request;

use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Http\Request\GetPropertiesRequestInterface;
use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\ItemInterface;

class GetPropertiesRequest extends GetResourceRequest implements GetPropertiesRequestInterface
{
    public function __construct(
        EnvironmentInterface $environment,
        ItemInterface $item
    ) {
        parent::__construct($environment, $item, ResourceRequestTypeEnum::PROPERTIES);
    }
}
