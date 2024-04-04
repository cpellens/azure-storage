<?php

namespace Azure\Storage\Library\Defaults\Http\Request;

use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\ItemInterface;
use Azure\Storage\Library\Defaults\Http\MethodEnum;

class GetResourceRequest extends AbstractResourceRequest
{
    public function __construct(
        EnvironmentInterface $environment,
        ItemInterface $item,
        ResourceRequestTypeEnum $requestTypeEnum
    ) {
        parent::__construct(MethodEnum::GET, $item, $requestTypeEnum);
    }
}
