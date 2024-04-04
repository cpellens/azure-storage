<?php

namespace Azure\Storage\Library\Defaults\Http\Request;

use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\ItemInterface;
use Azure\Storage\Api\Storage\ResourceTypeEnum;
use Azure\Storage\Library\Defaults\Http\MethodEnum;
use Azure\Storage\Library\Defaults\Http\Request;
use Azure\Storage\Library\Defaults\Http\Uri;
use Azure\Storage\Traits\Storage\EnvironmentAwareTrait;
use Azure\Storage\Traits\Storage\ItemAwareTrait;
use Psr\Http\Message\UriInterface;

abstract class AbstractResourceRequest extends Request
{
    use EnvironmentAwareTrait;
    use ItemAwareTrait;

    private string $apiVersion;

    public function __construct(
        MethodEnum $method,
        ItemInterface &$item,
        private readonly ?ResourceRequestTypeEnum $requestTypeEnum
    ) {
        $this->setItem($item);

        $this->headers['x-ms-meta-Name'] = [$item->getName()];

        parent::__construct($method->value, $this->createUri());
    }

    private function getName(): string
    {
        return $this->item->getName();
    }

    private function createUri(): UriInterface
    {
        $parameters = [
            'restype' => $this->getResourceType()->value
        ];

        if ($this->requestTypeEnum) {
            $parameters['comp'] = $this->requestTypeEnum->value;
        }

        return new Uri($this->getName() . '?' . http_build_query($parameters));
    }

    private function getResourceType(): ResourceTypeEnum
    {
        return $this->item->getResourceType();
    }

    public function getHeaders(): array
    {
        if (isset($this->apiVersion) && $this->apiVersion) {
            $this->headers['x-ms-api-version'] = [&$this->apiVersion];
        }

        return parent::getHeaders();
    }

    protected function setApiVersion(string $apiVersion): void
    {
        $this->apiVersion = $apiVersion;
    }
}
