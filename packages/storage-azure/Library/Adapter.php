<?php

namespace Azure\Storage\Library;

use ArrayAccess;
use ArrayObject;
use Azure\Storage\Api\AdapterInterface;
use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Storage\ContainerInterface;
use Azure\Storage\Api\Storage\ItemInterface;
use Azure\Storage\Library\Defaults\Http\CurlClient;
use Azure\Storage\Library\Defaults\Http\Request\CreateContainerRequest;
use Azure\Storage\Library\Storage\Blobs\Container;
use Override;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

/**
 * Class Adapter
 *
 * This class represents an adapter that handles server requests by sending them to an HTTP client.
 */
class Adapter implements AdapterInterface
{
    use LoggerAwareTrait;

    private readonly ArrayAccess $containers;

    public function __construct(
        private readonly EnvironmentInterface $environment
    ) {
        $this->containers = new ArrayObject();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
        $this->environment->setLogger($logger);
    }

    public function getContainer(string $containerName): ContainerInterface
    {
        if ($this->containers->offsetExists($containerName)) {
            return $this->containers->offsetGet($containerName);
        }

        $container = new Container($this->getEnvironment(), $containerName);
        $this->containers->offsetSet($containerName, $container);

        return $container;
    }

    public function getEnvironment(): EnvironmentInterface
    {
        return $this->environment;
    }

    /**
     * @throws ClientExceptionInterface
     */
    #[Override]
    public function store(ItemInterface $item): void
    {
        assert($item instanceof ContainerInterface, 'You cannot store items in the root');

        $request  = new CreateContainerRequest($this->getEnvironment(), $item);
        $response = $this->getEnvironment()->getHttpClient()->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            $responseText = $response->getBody()->getContents() ?: $response->getReasonPhrase();
            $responseText = $responseText ?: CurlClient::getCurlCommand($request);

            throw new UnexpectedValueException($responseText, $response->getStatusCode());
        }
    }
}
