<?php

namespace Azure\Storage\Unit;

use Azure\Storage\Api\AuthenticationInterface;
use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Storage\ContainerInterface;
use Azure\Storage\Library\Adapter;
use Azure\Storage\Library\Defaults\Environment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

#[CoversClass(Adapter::class)]
class AdapterTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    #[Test]
    public function testHandleSuccess(): void
    {
        $auth = $this->createMock(AuthenticationInterface::class);
        $auth->expects($this->once())
             ->method('authenticate');

        $environment = new Environment('', $auth);
        $adapter     = new Adapter($environment);

        $adapter->getEnvironment()->getHttpClient()->sendRequest($this->createConfiguredStub(RequestInterface::class, [
            'getUri' => $this->createStub(UriInterface::class)
        ]));
    }

    /**
     * @throws Exception
     */
    public function testGetContainer(): void
    {
        $environment = $this->createStub(EnvironmentInterface::class);
        $adapter     = new Adapter($environment);

        $container = $adapter->getContainer('test-container');
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function testSetHttpClient(): void
    {
        $http = $this->createMock(ClientInterface::class);
        $http->expects($this->once())->method('sendRequest');

        $env = $this->createConfiguredStub(EnvironmentInterface::class, [
            'getHttpClient' => $http
        ]);

        $adapter = new Adapter($env);
        $adapter->getEnvironment()
                ->getHttpClient()
                ->sendRequest($this->createStub(RequestInterface::class));
    }
}
