<?php

namespace Azure\Storage\Library\Defaults\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    private string $scheme = '';

    private string $authority = '';

    private string $userInfo = '';

    private string $host = '';

    private ?int $port = null;

    private string $path = '';

    private string $query = '';

    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        // parse the URI and update this URI with each component
        if ($uri === '') {
            return;
        }

        $parts = parse_url($uri);
        if (isset($parts['scheme'])) {
            $this->scheme = $parts['scheme'];
        }

        if (isset($parts['host'])) {
            $this->host = $parts['host'];
        }

        if (isset($parts['port'])) {
            $this->port = $parts['port'];
        }

        if (isset($parts['user'])) {
            $this->userInfo = $parts['user'];
            if (isset($parts['pass'])) {
                $this->userInfo .= ':' . $parts['pass'];
            }
        }

        if (isset($parts['path'])) {
            $this->path = $parts['path'];
        }

        if (isset($parts['query'])) {
            $this->query = $parts['query'];
        }

        if (isset($parts['fragment'])) {
            $this->fragment = $parts['fragment'];
        }
    }

    // Getters
    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        return $this->authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    // Fluent setters
    public function withScheme(string $scheme): Uri|static
    {
        $clone         = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    public function withUserInfo(string $user, ?string $password = null): Uri|static
    {
        $clone           = clone $this;
        $clone->userInfo =
            $password !== null && $password !== '' && $password !== '0' ? $user . ':' . $password : $user;
        return $clone;
    }

    public function withHost(string $host): Uri|static
    {
        $clone       = clone $this;
        $clone->host = $host;
        return $clone;
    }

    public function withPort(?int $port): Uri|static
    {
        $clone       = clone $this;
        $clone->port = $port;
        return $clone;
    }

    public function withFragment(string $fragment): Uri|static
    {
        $clone           = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    public function withQuery(string $query): Uri|static
    {
        $clone        = clone $this;
        $clone->query = $query;
        return $clone;
    }

    public function __toString(): string
    {
        return implode(
            '',
            [
                $this->scheme ? "{$this->scheme}://" : '',
                $this->authority ? "{$this->authority}:{$this->host}" : $this->host,
                $this->port ? ":{$this->port}" : '',
                '/' . trim($this->path ?? '', '/'),
                $this->query !== '' ? '?' . $this->query : '',
                $this->fragment !== '' ? '#' . $this->fragment : ''
            ]
        );
    }

    public function withPath(string $path): Uri|static
    {
        $clone       = clone $this;
        $clone->path = $path;

        return $clone;
    }
}
