<?php

namespace Azure\Storage\Library\Storage\Blobs;

use ArrayAccess;
use ArrayObject;
use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Http\Request\ResourceRequestTypeEnum;
use Azure\Storage\Api\Storage\BlobInterface;
use Azure\Storage\Api\Storage\ContainerInterface;
use Azure\Storage\Api\Storage\ItemInterface;
use Azure\Storage\Api\Storage\ResourceTypeEnum;
use Azure\Storage\Library\Defaults\Http\Request\GetMetadataRequest;
use Azure\Storage\Library\Defaults\Http\Request\GetPropertiesRequest;
use Azure\Storage\Library\Defaults\Http\Request\GetResourceRequest;
use Azure\Storage\Library\Defaults\Http\Response\ListBlobsResponse;
use Azure\Storage\Traits\Storage\MetadataAwareTrait;
use Azure\Storage\Traits\Storage\ResourceTypeTrait;
use DateTime;
use DateTimeInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\SerializerInterface;
use UnexpectedValueException;

class Container implements ContainerInterface
{
    private ArrayAccess $metadata;
    private ArrayObject $properties;

    use SerializerAwareTrait;
    use ResourceTypeTrait;
    use MetadataAwareTrait;

    public function __construct(
        EnvironmentInterface $environment,
        private readonly string $containerName
    ) {
        $this->environment = $environment;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function list(): iterable
    {
        $request  = new GetResourceRequest($this->environment, $this, ResourceRequestTypeEnum::LIST);
        $response = $this->environment->getHttpClient()->sendRequest($request);

        $responseStatus = $response->getStatusCode();
        if ($responseStatus !== 200) {
            throw new UnexpectedValueException($response->getReasonPhrase(), $responseStatus);
        }

        return $this->getSerializer()->deserialize(
            $response->getBody(),
            ListBlobsResponse::class,
            XmlEncoder::FORMAT,
            [
                XmlEncoder::ROOT_NODE_NAME => ListBlobsResponse::STR_ROOT_TAG
            ]
        );
    }

    private function getSerializer(): SerializerInterface
    {
        if (!isset($this->serializer)) {
            $this->setupSerializer();
        }

        return $this->serializer;
    }

    private function setupSerializer(): void
    {
        $xml        = new XmlEncoder([
            XmlEncoder::ENCODING      => 'UTF-8',
            XmlEncoder::AS_COLLECTION => true
        ]);
        $normalizer = new ObjectNormalizer(new ClassMetadataFactory(new AttributeLoader()));

        $this->serializer = new Serializer([$normalizer], [$xml]);
    }

    public function get(string $itemName): BlobInterface
    {
        return new Blob($this->getEnvironment(), $this, $itemName);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getProperties(): ArrayAccess
    {
        if (!isset($this->properties)) {
            $request  = new GetPropertiesRequest($this->environment, $this);
            $response = $this->environment->getHttpClient()->sendRequest($request);

            $this->properties = new ArrayObject($response->getHeaders());
        }

        return clone $this->properties;
    }

    public function getResourceType(): ResourceTypeEnum
    {
        return ResourceTypeEnum::CONTAINER;
    }

    public function store(ItemInterface $item): void
    {
        // TODO: Implement store() method.
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function isPublic(): bool
    {
        $metadata = $this->getMetadata();
        return $metadata['public'];
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return new DateTime();
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return new DateTime();
    }

    public function getId(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->containerName;
    }

    /**
     * @throws ClientExceptionInterface
     */
    protected function loadMetadata(): void
    {
        static $headerPrefix = 'x-ms-meta-';

        $request  = new GetMetadataRequest($this->environment, $this);
        $response = $this->environment->getHttpClient()
                                      ->sendRequest($request);

        // throw an error if the response isn't successful
        if ($response->getStatusCode() !== 200) {
            throw new UnexpectedValueException($response->getReasonPhrase(), $response->getStatusCode());
        }

        $responseHeaders = $response->getHeaders();
        if (!$responseHeaders) {
            return;
        }

        // filter headers down to only the keys that start with the headerPrefix
        $responseHeaders = array_filter(
            $responseHeaders,
            static fn(string $header) => str_starts_with($header, $headerPrefix),
            ARRAY_FILTER_USE_KEY
        );

        $meta = new ArrayObject();
        foreach ($responseHeaders as $key => $value) {
            if (str_starts_with($key, $headerPrefix)) {
                $meta->offsetSet(substr($key, strlen($headerPrefix)), implode(', ', $value));
            }
        }

        $this->setMetadata($meta);
    }

    public function setMetadata(ArrayAccess $arrayAccess): void
    {
        $this->metadata = clone $arrayAccess;
    }
}
