<?php

namespace Azure\Storage\Library\Defaults\Http\Request;

use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Http\Request\GetMetadataRequestInterface;
use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\ItemInterface;

class GetMetadataRequest extends GetResourceRequest implements GetMetadataRequestInterface
{
    public function __construct(EnvironmentInterface $environment, ItemInterface $item)
    {
        parent::__construct($environment, $item, ResourceRequestTypeEnum::METADATA);
    }
}
