<?php

namespace Azure\Storage\Library\Defaults\Http;

use Azure\Storage\Api\EnvironmentInterface;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

#[RequiresPhpExtension('curl')]
class CurlClient implements ClientInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly EnvironmentInterface $environment,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public static function getCurlCommand(RequestInterface $request): string
    {
        $uri     = $request->getUri();
        $method  = $request->getMethod();
        $headers = $request->getHeaders();
        $body    = $request->getBody()->getContents();

        $curlCommand = sprintf('curl -X %s ', $method);
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $curlCommand .= sprintf("-H '%s: %s' ", $name, $value);
            }
        }

        if ($body) {
            $curlCommand .= sprintf("-d '%s'", addslashes($body));
        }

        return $curlCommand . sprintf("'%s'", $uri);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $protocolVersion = match ($request->getProtocolVersion()) {
            '1.0'   => CURL_HTTP_VERSION_1_0,
            '2'     => CURL_HTTP_VERSION_2,
            '2.0'   => CURL_HTTP_VERSION_2_0,
            default => CURL_HTTP_VERSION_1_1,
        };

        $body    = $request->getBody();
        $request = $request->withUri($this->environment->createUri($request->getUri()));
        $this->environment->getAuthenticator()->authenticate($request);
        $parsedUrl = (string)$request->getUri();

        $headerStrings = [];
        foreach ($request->getHeaders() as $headerKey => $headerArray) {
            $headerStrings[] = sprintf(
                '%s: %s',
                $headerKey,
                $request->getHeaderLine($headerKey)
            );
        }

        $handle = curl_init();
        curl_setopt_array(
            $handle,
            $opts = [
                CURLOPT_URL                   => $parsedUrl,
                CURLOPT_RETURNTRANSFER        => true,
                CURLOPT_HEADER                => true,
                CURLOPT_CUSTOMREQUEST         => $request->getMethod(),
                CURLOPT_HTTP_VERSION          => $protocolVersion,
                CURLOPT_HTTPHEADER            => $headerStrings,
                CURLOPT_POSTFIELDS            => (int)$body?->getSize() > 0 ? $request->getBody()->getContents() : null,
                CURLOPT_SSLVERSION            => CURL_SSLVERSION_TLSv1_3,
                CURLOPT_HTTP_CONTENT_DECODING => true,
                CURLOPT_FOLLOWLOCATION        => true,
                CURLOPT_COOKIE                => true
            ]
        );

        $this->logger?->info(
            sprintf('[%s] %s', $request->getMethod(), $parsedUrl),
            [
                'options' => $opts
            ]
        );

        $curlResponse        = curl_exec($handle);
        $responseHeadersSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $responseHeaders     = substr($curlResponse, 0, $responseHeadersSize);
        $responseBody        = substr($curlResponse, $responseHeadersSize);

        $response = $this->responseFactory->createResponse()
                                          ->withStatus(curl_getinfo($handle, CURLINFO_HTTP_CODE))
                                          ->withBody($this->environment->createStream($responseBody))
                                          ->withProtocolVersion(curl_getinfo($handle, CURLINFO_HTTP_VERSION));

        $headers = explode(PHP_EOL, $responseHeaders);
        foreach ($headers as $header) {
            if ($header !== '' && $header !== '0') {
                [$name, $value] = explode(':', $header, 2);
                $response = $response->withAddedHeader(trim($name), trim($value));
            }
        }

        curl_close($handle);

        $this->logger?->info($responseBody, $headers);

        return $response;
    }
}
