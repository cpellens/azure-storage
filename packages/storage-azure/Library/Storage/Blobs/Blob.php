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
use Azure\Storage\Library\Defaults\Http\Response;
use DateTimeInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use UnexpectedValueException;

class Blob extends Response implements BlobInterface
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
        return $this->name;
    }

    public function getContentType(): string
    {
        return $this->getHeaderLine('Content-Type');
    }

    public function setContentType(string $contentType): void
    {
        $this->withHeader('Content-Type', $contentType);
    }

    public function getId(): string
    {
        return $this->getHeaderLine('x-ms-id');
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->getProperties()['created_at'];
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

    /**
     * @throws ClientExceptionInterface
     */
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->getProperties()['updated_at'];
    }

    public function setMetadata(ArrayAccess $arrayAccess): void
    {
    }

    public function getVersion(): string
    {
        return $this->getMetadata()['version'];
    }

    public function getMetadata(): ArrayAccess
    {
        $array = new ArrayObject();

        foreach ($this->getHeaders() as $header => $_) {
            if (!str_starts_with($header, 'x-meta-')) {
                continue;
            }

            $array[$header] = $this->getHeaderLine($header);
        }

        return $array;
    }

    public function getResourceType(): ResourceTypeEnum
    {
        return ResourceTypeEnum::BLOB;
    }
}
