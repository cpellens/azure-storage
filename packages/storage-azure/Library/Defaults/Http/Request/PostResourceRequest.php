<?php

namespace Azure\Storage\Library\Defaults\Http\Request;

use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\ItemInterface;
use Azure\Storage\Library\Defaults\Http\MethodEnum;
use Azure\Storage\Library\Defaults\Http\Stream;
use Psr\Http\Message\StreamInterface;

class PostResourceRequest extends AbstractResourceRequest
{
    public function __construct(
        ItemInterface $item,
        ResourceRequestTypeEnum $requestTypeEnum
    ) {
        parent::__construct(MethodEnum::POST, $item, $requestTypeEnum);
    }

    public function getBody(): StreamInterface
    {
        if ($this->body === null) {
            $this->body = new Stream(fopen('php://memory', 'r+'));
        }

        return parent::getBody();
    }

    public function __destruct()
    {
        $this->body?->close();
    }
}
