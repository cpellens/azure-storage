<?php

namespace Azure\Storage\Library\Defaults;

use Azure\Storage\Api\AuthenticationInterface;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Psr\Http\Message\RequestInterface;
use SensitiveParameter;

readonly class SharedKeyAuthentication implements AuthenticationInterface
{
    // String template constant to SharedKey header value
    public const string SHARED_KEY_TEMPLATE = 'SharedKey %s';

    // String template constant for header name
    public const string HEADER_NAME = 'Authorization';

    public function __construct(
        private string $accountName,
        #[SensitiveParameter] private string $apiKey
    ) {
    }

    public function authenticate(RequestInterface &$request = null): void
    {
        $dateTimeZone = new DateTimeZone('UTC');
        $date         = (new DateTime('now'))
            ->setTimezone($dateTimeZone)
            ->format(DateTimeInterface::RFC7231);

        // Remove existing Authorization header if it exists
        $request = $request->withoutHeader(static::HEADER_NAME)
                           ->withHeader('Date', $date);

        $body    = $request->getBody();
        if ($body?->getSize() > 0) {
            $request = $request->withHeader('content-md5', md5($body->getContents()))
                               ->withHeader('content-length', $body->getSize())
                               ->withHeader('content-encoding', 'UTF-8');
        }

        $request = $request->withHeader(
            static::HEADER_NAME,
            sprintf(static::SHARED_KEY_TEMPLATE, $this->getAuthenticationString($request))
        );
    }

    private function getAuthenticationString(RequestInterface $serverRequest): string
    {
        // return the account name and the signature separated by a colon
        return $this->accountName . ':' . $this->getSignature($serverRequest);
    }

    private function getSignature(RequestInterface $serverRequest): string
    {
        // create a signature to authenticate with azure blob storage using a shared key
        return base64_encode(
            hash_hmac(
                'sha256',
                mb_convert_encoding(
                    $this->getCanonicalizedString($serverRequest),
                    'UTF-8'
                ),
                base64_decode($this->apiKey),
                true
            )
        );
    }

    private function getCanonicalizedString(RequestInterface $serverRequest): string
    {
        $contentLength = (int)$serverRequest->getHeaderLine('Content-Length');
        if ($contentLength === 0) {
            $contentLength = '';
        }

        $canonicalizedString = [];

        $canonicalizedString [] = strtoupper($serverRequest->getMethod());
        $canonicalizedString [] = $serverRequest->getHeaderLine('Content-Encoding');
        $canonicalizedString [] = $serverRequest->getHeaderLine('Content-Language');
        $canonicalizedString [] = $contentLength;
        $canonicalizedString [] = $serverRequest->getHeaderLine('Content-MD5');
        $canonicalizedString [] = $serverRequest->getHeaderLine('Content-Type');
        $canonicalizedString [] = $serverRequest->getHeaderLine('Date');
        $canonicalizedString [] = $serverRequest->getHeaderLine('If-Modified-Since');
        $canonicalizedString [] = $serverRequest->getHeaderLine('If-Match');
        $canonicalizedString [] = $serverRequest->getHeaderLine('If-None-Match');
        $canonicalizedString [] = $serverRequest->getHeaderLine('If-Unmodified-Since');
        $canonicalizedString [] = $serverRequest->getHeaderLine('Range');
        $canonicalizedString [] = $this->getCanonicalizedHeaders($serverRequest);
        $canonicalizedString [] = $this->getCanonicalizedResource($serverRequest);
        return implode(PHP_EOL, $canonicalizedString);
    }

    private function getCanonicalizedHeaders(RequestInterface $serverRequest): string
    {
        $canonicalizedHeaders = [];
        $headers              = array_change_key_case($serverRequest->getHeaders());
        ksort($headers);
        foreach ($headers as $name => $values) {
            if (str_starts_with($name, 'x-ms-')) {
                $canonicalizedHeaders [] = rtrim($name) . ':' . ltrim(implode(',', array_map('trim', $values)));
            }
        }

        return implode("\n", $canonicalizedHeaders);
    }

    private function getCanonicalizedResource(RequestInterface $serverRequest): string
    {
        $uri                   = $serverRequest->getUri();
        $canonicalizedResource = '/' . $this->getAccountName() . '/' . ltrim($uri->getPath(), '/');
        $query                 = $uri->getQuery();
        if ($query) {
            // parse the query into variables
            parse_str($query, $queryVariables);
            $queryVariables = array_change_key_case($queryVariables);
            if ($queryVariables !== []) {
                ksort($queryVariables);
                $canonicalizedQuery = [];
                foreach ($queryVariables as $key => $value) {
                    $canonicalizedQuery[] = $key . ':' . $value;
                }

                $canonicalizedResource .= "\n" . implode("\n", $canonicalizedQuery);
            }
        }

        return $canonicalizedResource;
    }

    private function getAccountName(): string
    {
        return $this->accountName;
    }
}
