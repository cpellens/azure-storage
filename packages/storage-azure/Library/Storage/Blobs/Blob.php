<?php

namespace Azure\Storage\Library\Storage\Blobs;

use ArrayAccess;
use ArrayObject;
use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\BlobInterface;
use Azure\Storage\Api\Storage\ContainerInterface;
use Azure\Storage\Api\Storage\ResourceTypeEnum;
use Azure\Storage\Library\Defaults\Http\Request\GetResourceRequest;
use DateTimeInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use UnexpectedValueException;

class Blob implements BlobInterface
{
    use SerializerAwareTrait;

    public function __construct(
        private readonly EnvironmentInterface $environment,
        private readonly ContainerInterface $container,
        private readonly string $name
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getStream(): StreamInterface
    {
        $request = $this->environment->createRequest(
            'GET',
            $this->container->getName() . '/' . $this->name
        );

        $response = $this->environment->getHttpClient()->sendRequest($request);
        return $response->getBody();
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function getContentType(): string
    {
        // TODO: Implement getContentType() method.
    }

    public function setContentType(string $contentType): void
    {
        // TODO: Implement setContentType() method.
    }

    public function getId(): string
    {
        // TODO: Implement getId() method.
    }

    public function getCreatedAt(): DateTimeInterface
    {
        // TODO: Implement getCreatedAt() method.
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        // TODO: Implement getUpdatedAt() method.
    }

    public function getMetadata(): ArrayAccess
    {
        // TODO: Implement getMetadata() method.
    }

    public function setMetadata(ArrayAccess $arrayAccess): void
    {
        // TODO: Implement setMetadata() method.
    }

    public function getVersion(): string
    {
        // TODO: Implement getVersion() method.
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getProperties(): ArrayAccess
    {
        $request = new GetResourceRequest(
            $this->environment,
            $this,
            ResourceRequestTypeEnum::PROPERTIES
        );

        $response = $this->environment->getHttpClient()->sendRequest($request);

        // ensure a successful response
        if ($response->getStatusCode() !== 200) {
            throw new UnexpectedValueException('Failed to get blob properties');
        }

        return new ArrayObject($response->getHeaders());
    }


    public function getResourceType(): ResourceTypeEnum
    {
        return ResourceTypeEnum::BLOB;
    }
}
