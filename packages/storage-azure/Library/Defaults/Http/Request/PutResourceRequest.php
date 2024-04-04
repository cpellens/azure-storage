<?php

namespace Azure\Storage\Library\Defaults\Http\Request;

use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\ItemInterface;
use Azure\Storage\Library\Defaults\Http\MethodEnum;
use Azure\Storage\Library\Defaults\Http\Stream;
use Psr\Http\Message\StreamInterface;

class PutResourceRequest extends AbstractResourceRequest
{
    public function __construct(
        EnvironmentInterface $environment,
        ItemInterface $item,
        ?ResourceRequestTypeEnum $requestTypeEnum = null
    ) {
        parent::__construct(MethodEnum::PUT, $item, $requestTypeEnum);
    }

    public function getBody(): StreamInterface
    {
        if ($this->body === null) {
            return new Stream(null);
        }

        return parent::getBody();
    }

    public function __destruct()
    {
        $this->body?->close();
    }
}
