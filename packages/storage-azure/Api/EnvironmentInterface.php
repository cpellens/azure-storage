<?php

namespace Azure\Storage\Api;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;

interface EnvironmentInterface extends UriFactoryInterface,
                                       RequestFactoryInterface,
                                       StreamFactoryInterface,
                                       LoggerAwareInterface
{
    public function getAuthenticator(): AuthenticationInterface;

    public function setAuthenticator(AuthenticationInterface $authentication): void;

    public function getHttpClient(): ClientInterface;

    public function setHttpClient(ClientInterface $client): void;

    public function setCacheAdapter(CacheItemPoolInterface $cacheItemPool): void;

    public function getCacheAdapter(): CacheItemPoolInterface;

    public function getApiEndpoint(): UriInterface;
}
