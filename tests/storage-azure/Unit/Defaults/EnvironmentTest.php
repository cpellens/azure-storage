<?php

namespace Azure\Storage\Unit\Defaults;

use Azure\Storage\Api\AuthenticationInterface;
use Azure\Storage\Library\Defaults\Environment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriInterface;

#[CoversClass(Environment::class)]
class EnvironmentTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetSetAuthenticator(): void
    {
        $authA       = $this->createStub(AuthenticationInterface::class);
        $authB       = $this->createStub(AuthenticationInterface::class);
        $environment = new Environment(
            'test',
            $authA,
            $this->createStub(ResponseFactoryInterface::class),
            $this->createStub(CacheItemPoolInterface::class)
        );

        $this->assertSame($authA, $environment->getAuthenticator());
        $environment->setAuthenticator($authB);
        $this->assertSame($authB, $environment->getAuthenticator());
    }

    /**
     * @throws Exception
     */
    public function testCreateHttpClient(): void
    {
        $environment = new Environment(
            'https://charlespellens.com',
            $this->createStub(AuthenticationInterface::class)
        );
        $client      = $environment->getHttpClient();
        $this->assertInstanceOf(ClientInterface::class, $client);
    }


    /**
     * @throws Exception
     */
    public function testCreateUri(): void
    {
        $environment = new Environment(
            'https://example.com',
            $this->createStub(AuthenticationInterface::class),
            $this->createStub(CacheItemPoolInterface::class)
        );
        $uri         = $environment->createUri('/path/to/resource');

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertEquals('https://example.com/path/to/resource', (string)$uri);

        // now test with a fragment
        $uriWithFragment = $environment->createUri('/path/to/resource#fragment');
        $this->assertInstanceOf(UriInterface::class, $uriWithFragment);
        $this->assertEquals('https://example.com/path/to/resource#fragment', (string)$uriWithFragment);

        // test an environment with a path already
        $environmentWithPath = new Environment(
            'https://example.com/path',
            $this->createStub(AuthenticationInterface::class),
            $this->createStub(CacheItemPoolInterface::class)
        );
        $uriWithPath         = $environmentWithPath->createUri('/to/resource');
        $this->assertInstanceOf(UriInterface::class, $uriWithPath);
        $this->assertEquals('https://example.com/path/to/resource', (string)$uriWithPath);

        // test with a query string
        $uriWithQuery = $environment->createUri('/path/to/resource?param1=value1&param2=value2');
        $this->assertInstanceOf(UriInterface::class, $uriWithQuery);
        $this->assertEquals('https://example.com/path/to/resource?param1=value1&param2=value2', (string)$uriWithQuery);
    }
}
