<?php

namespace Azure\Storage\Library\Defaults\Http;

use Amp\ByteStream\BufferException;
use Amp\ByteStream\StreamException;
use Amp\Http\Client\Connection\UnlimitedConnectionPool;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class AmphpClient implements ClientInterface
{
    private static UnlimitedConnectionPool $unlimitedConnectionPool;

    private readonly HttpClient $httpClient;

    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
        $httpClientBuilder = new HttpClientBuilder();

        $this->httpClient = $httpClientBuilder->followRedirects()
                                              ->usingPool(
                                                  static::$unlimitedConnectionPool ??= new UnlimitedConnectionPool()
                                              )
                                              ->build();
    }

    /**
     * @throws BufferException
     * @throws StreamException
     * @throws HttpException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // convert the PSR request to an Amphp one
        $amphpRequest = new Request(
            $request->getUri(),
            $request->getMethod(),
            $request->getBody()
        );
        $amphpRequest->setHeaders($request->getHeaders());

        $amphpResponse = $this->httpClient->request($amphpRequest);
        $response      =
            $this->responseFactory->createResponse($amphpResponse->getStatus(), $amphpResponse->getReason());

        // load headers into response
        $headers = $amphpResponse->getHeaders();
        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        // read the body into memory
        $stream = new Stream(null);
        while ($body = $amphpResponse->getBody()->read()) {
            $stream->write($body);
        }
        $stream->rewind();

        // return the constructed response
        return $response->withBody($stream)
                        ->withStatus($amphpResponse->getStatus(), $amphpResponse->getReason())
                        ->withProtocolVersion($amphpResponse->getProtocolVersion());
    }
}
