<?php

namespace Azure\Storage\Library\Defaults;

use Azure\Storage\Api\AuthenticationInterface;
use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Library\Defaults\Http\AmphpClient;
use Azure\Storage\Library\Defaults\Http\CurlClient;
use Azure\Storage\Library\Defaults\Http\Request;
use Azure\Storage\Library\Defaults\Http\ResponseFactory;
use Azure\Storage\Library\Defaults\Http\Stream;
use Azure\Storage\Library\Defaults\Http\Uri;
use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Environment implements EnvironmentInterface
{
    private ClientInterface $client;

    use LoggerAwareTrait;

    public function __construct(
        private readonly string|UriInterface $endpoint,
        private AuthenticationInterface $authentication,
        private readonly ResponseFactoryInterface $responseFactory = new ResponseFactory(),
        private ?CacheItemPoolInterface $cacheItemPool = null,
    ) {
    }

    public function getAuthenticator(): AuthenticationInterface
    {
        return $this->authentication;
    }

    public function setAuthenticator(AuthenticationInterface $authentication): void
    {
        $this->authentication = $authentication;
    }

    public function setHttpClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    public function setCacheAdapter(CacheItemPoolInterface $cacheItemPool): void
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function getCacheAdapter(): CacheItemPoolInterface
    {
        return $this->cacheItemPool;
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        // if the URI is a string, then parse it into its components
        if (is_string($uri)) {
            $uri = $this->createUri($uri);
        }

        $this->logger?->info('Creating request', ['method' => $method, 'uri' => $uri]);

        // assign the uri and method to the request
        return new Request($method, $uri);
    }

    public function createUri(string $uri = ''): UriInterface
    {
        $restEndpoint = new Uri($this->getRestEndpoint());

        $query = trim(parse_url($uri, PHP_URL_QUERY));
        $path  = trim(parse_url($uri, PHP_URL_PATH), '/');

        if ($query !== '' && $query !== '0') {
            parse_str($query, $params);
            $params = array_change_key_case($params);
            ksort($params);

            $restEndpoint = $restEndpoint->withQuery(
                http_build_query($params, encoding_type: PHP_QUERY_RFC3986)
            );
        }

        // check if there's a fragment in $uri then set a variable
        $fragment = trim(parse_url($uri, PHP_URL_FRAGMENT));

        // if the rest endpoint has a path, prepend it to $path
        if ($restEndpoint->getPath() !== '') {
            $path = $restEndpoint->getPath() . '/' . trim($path, '/');
        }

        return $restEndpoint->withPath($path)->withFragment($fragment);
    }

    public function getRestEndpoint(): string
    {
        return $this->endpoint;
    }

    public function createStream(string $content = ''): StreamInterface
    {
        // create an in-memory handle then write the content to it
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        return $this->createStreamFromResource($handle);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        // ensure that the file exists
        if (!file_exists($filename)) {
            throw new InvalidArgumentException('File does not exist: ' . $filename);
        }

        return $this->createStreamFromResource(fopen($filename, $mode));
    }

    public function getApiEndpoint(): UriInterface
    {
        if ($this->endpoint instanceof UriInterface) {
            return clone $this->endpoint;
        }

        return new Uri($this->endpoint);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;

        $client = $this->getHttpClient();
        if ($client instanceof LoggerAwareInterface) {
            $client->setLogger($logger);
        }
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->client ??= $this->createHttpClient();
    }

    private function createHttpClient(): ClientInterface
    {
        if (extension_loaded('curl')) {
            return new CurlClient($this, $this->responseFactory);
        }

        return new AmphpClient();
    }
}
