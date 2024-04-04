<?php

namespace Azure\Storage\Traits\Storage;

use Azure\Storage\Api\Storage\ResourceTypeEnum;
use Psr\Http\Message\UriInterface;

trait ResourceTypeTrait
{
    use EnvironmentAwareTrait;

    public const string QUERY_STRING_KEY_RESOURCE_TYPE = 'restype';

    protected function createUriFromComp(string $comp): UriInterface
    {
        return $this->createUri(parameters: [
            'comp' => $comp
        ]);
    }

    private function createUri(string $uri = null, array $parameters = []): UriInterface
    {
        $parameters[self::QUERY_STRING_KEY_RESOURCE_TYPE] = $this->getResourceType()->value;

        return $this->environment->createUri(
            sprintf(
                '%s?%s',
                trim(implode('/', [$this->getName(), $uri]), '/'),
                http_build_query($parameters)
            )
        );
    }

    abstract protected function getResourceType(): ResourceTypeEnum;

    abstract public function getName(): string;
}
