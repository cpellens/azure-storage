<?php

namespace Azure\Storage\IntegrationTests;

use Azure\Storage\Api\AdapterInterface;
use Azure\Storage\Api\Storage\ContainerInterface;
use Azure\Storage\Library\Adapter;
use Azure\Storage\Library\Defaults\Environment;
use Azure\Storage\Library\Defaults\SharedKeyAuthentication;
use Azure\Storage\Library\Storage\Blobs\Container;
use Azure\Storage\Library\Storage\Cache\ArrayAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Container::class)]
class BlobTest extends TestCase
{
    private AdapterInterface $adapter;


    public function testCreateContainer(): void
    {
        // try creating a container named `test-container`
        $container = $this->adapter->getContainer('test-container');
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertSame('test-container', $container->getName());

        $this->adapter->store($container);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $authentication = new SharedKeyAuthentication(
            'devstoreaccount1',
            'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw=='
        );
        $environment    = new Environment(
            'http://localhost:10000/devstoreaccount1',
            $authentication,
            cacheItemPool: new ArrayAdapter()
        );
        $this->adapter  = new Adapter($environment);
    }
}
