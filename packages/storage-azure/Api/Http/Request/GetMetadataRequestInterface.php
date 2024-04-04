<?php

namespace Azure\Storage\Api\Http\Request;

use Azure\Storage\Api\Storage\ItemInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @template T of ItemInterface
 */
interface GetMetadataRequestInterface extends RequestInterface
{
}
