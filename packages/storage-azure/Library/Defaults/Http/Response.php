<?php

namespace Azure\Storage\Library\Defaults\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    private string           $protocolVersion = '1.1';

    private array            $headers         = [];

    private ?StreamInterface $stream            = null;

    private int              $statusCode      = 200;

    private string           $reasonPhrase    = '';

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): self
    {
        $new                  = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeaderLine(string $name): string
    {
        if ($this->hasHeader($name)) {
            return implode(", ", $this->headers[$name]);
        }

        return '';
    }

    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    public function withHeader(string $name, $value): self
    {
        $new = clone $this;

        // ensure that value is an array
        if (!is_array($value)) {
            $value = [$value];
        }

        $new->headers[$name] = $value;
        return $new;
    }

    public function withAddedHeader(string $name, $value): self
    {
        $new = clone $this;

        // ensure that value is an array
        if (!is_array($value)) {
            $value = [$value];
        }

        $new->headers[$name] = ($currentValues = $new->getHeader($name)) !== [] ? array_merge($currentValues, $value) : $value;

        return $new;
    }

    public function getHeader(string $name): array
    {
        return $this->headers[$name] ?? [];
    }

    public function withoutHeader(string $name): self
    {
        $new = clone $this;
        unset($new->headers[$name]);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    public function withBody(StreamInterface $body): self
    {
        $new       = clone $this;
        $new->stream = $body;
        return $new;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): self
    {
        $new               = clone $this;
        $new->statusCode   = $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

}
