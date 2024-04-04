<?php

namespace Azure\Storage\Library\Defaults\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class Request implements RequestInterface
{
    protected ?StreamInterface $body    = null;
    protected array            $headers = [];
    private string             $version = '1.1';
    private string             $requestTarget;

    public function __construct(private string $method, private UriInterface $uri)
    {
    }

    /**
     * @throws RuntimeException
     */
    public function getProtocolVersion(): string
    {
        if ($this->version === '' || $this->version === '0') {
            throw new InvalidArgumentException('No protocol version set');
        }

        return $this->version;
    }

    public function withProtocolVersion(string $version): self
    {
        $clone          = clone $this;
        $clone->version = $version;
        return $clone;
    }

    public function getHeaders(): array
    {
        $headers = $this->headers;
        ksort($headers);
        return $headers;
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function getHeader(string $name): array
    {
        if ($this->hasHeader($name)) {
            return $this->headers[$name];
        }

        return [];
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    public function withAddedHeader(string $name, $value): self
    {
        $clone = clone $this;

        if ($clone->hasHeader($name)) {
            // if the value isn't an array, wrap it into an array
            if (!is_array($value)) {
                $value = [$value];
            }

            $clone->headers[$name] = array_merge($clone->headers[$name], $value);
            return $clone;
        }

        return $clone->withHeader($name, $value);
    }

    public function withHeader(string $name, $value): self
    {
        $clone = clone $this;
        // if the value isn't an array, wrap it into an array
        if (!is_array($value)) {
            $value = [$value];
        }

        $clone->headers[$name] = $value;
        return $clone;
    }

    public function withoutHeader(string $name): self
    {
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }

    public function getBody(): ?StreamInterface
    {
        return $this->body ?? null;
    }

    public function withBody(StreamInterface $body): self
    {
        $clone = clone $this;

        $clone->body = $body;
        return $clone;
    }

    /**
     * @throws RuntimeException
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget === '' || $this->requestTarget === '0') {
            throw new InvalidArgumentException('No request target assigned');
        }

        return $this->requestTarget;
    }

    public function withRequestTarget(string $requestTarget): self
    {
        $clone                = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    /**
     * @throws RuntimeException
     */
    public function getMethod(): string
    {
        if ($this->method === '' || $this->method === '0') {
            return MethodEnum::GET->value;
        }

        return $this->method;
    }

    public function withMethod(string $method): self
    {
        $clone         = clone $this;
        $clone->method = $method;
        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): self
    {
        $clone      = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            $clone->withHeader('Host', $uri->getHost());
        }

        return $clone;
    }
}
