<?php

namespace Azure\Storage\Unit\Utilities;

use ArrayObject;
use Azure\Storage\Library\Storage\Cache\ArrayAdapter;
use Azure\Storage\Library\Utilities\CacheProxy;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\AbstractLogger;

#[CoversClass(CacheProxy::class)]
class CacheProxyTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSimpleProxy(): void
    {
        // Create a mock cache pool
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);

        $cacheProxy = CacheProxy::createFromClass(ArrayObject::class, $cacheItemPool);

        // Expect that save was called
        $cacheItemPool->expects($this->once())
                      ->method('save');
        $cacheProxy->offsetGet(0);
    }

    public function testArrayAdapter(): void
    {
        // Create a mock cache pool
        $arrayAdapter = new ArrayAdapter();
        $cacheProxy   = CacheProxy::createFromClass(ArrayObject::class, $arrayAdapter);

        $cacheProxy->offsetSet(0, 'hello');

        // Assert that offsetGet(0) returns 'hello'
        $this->assertEquals('hello', $cacheProxy->offsetGet(0));
    }

    /**
     * @throws Exception
     */
    public function testInvalidClassName(): void
    {
        // Create a mock cache pool
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $this->expectException(InvalidArgumentException::class);
        CacheProxy::createFromClass('InvalidClassName', $cacheItemPool);
    }

    /**
     * @throws Exception
     */
    public function testValidNotInstantiableClassName(): void
    {
        // Create a mock cache pool
        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $this->expectExceptionMessage(CacheProxy::ERROR_INVALID_ACCESS);
        CacheProxy::createFromClass(AbstractLogger::class, $cacheItemPool);
    }

    // Test that the cache item was returned on the second call
    public function testCacheItemReturnedOnSecondCall(): void
    {
        $arrayObject = new ArrayObject();
        $arrayObject->offsetSet(0, 'Before');

        $arrayAdapter = new ArrayAdapter();
        $cacheProxy   = CacheProxy::createFromInstance($arrayObject, $arrayAdapter);

        $this->assertSame('Before', $cacheProxy->offsetGet(0));
        $arrayObject->offsetSet(0, 'After');
        $this->assertSame('After', $cacheProxy->offsetGet(0));
    }
}
