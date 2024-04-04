<?php

namespace Azure\Storage\Library\Utilities;

use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionException;

/**
 * @template TClassName
 */
abstract class CacheProxy
{
    public const ERROR_INVALID_CLASS_NAME = 'Invalid class name provided';

    public const ERROR_INVALID_ACCESS     = 'Class not instantiable';

    /**
     * @var TClassName
     */
    protected mixed $instance = null;

    /**
     * @var ReflectionClass<TClassName>
     */
    private readonly ReflectionClass $reflectionClass;

    /**
     * @param class-string<TClassName> $inputClassFQN
     * @return TClassName
     */
    public static function createFromClass(string $inputClassFQN, CacheItemPoolInterface $cacheItemPool): CacheProxy
    {
        // Create an anonymous instance of a class that extends CacheProxy and pass in the parameters to its constructor
        return new class($inputClassFQN, $cacheItemPool) extends CacheProxy {
            public function __construct(string $className, CacheItemPoolInterface $cacheItemPool)
            {
                parent::__construct($className, $cacheItemPool);
            }
        };
    }

    /**
     * @param class-string<TClassName> $className
     */
    protected function __construct(
        private readonly string $className,
        private readonly CacheItemPoolInterface $cacheItemPool
    ) {
        // Check if the supplied class FQN is from user input
        if (!class_exists($this->className)) {
            throw new InvalidArgumentException(self::ERROR_INVALID_CLASS_NAME);
        }

        // Get the reflection class from className
        $this->reflectionClass = new ReflectionClass($this->className);

        // Ensure that className is actually a class FQN
        if (!$this->reflectionClass->isInstantiable()) {
            throw new InvalidArgumentException(self::ERROR_INVALID_ACCESS);
        }
    }

    /**
     * @psalm-param TClassName $instance
     * @return TClassName
     */
    public static function createFromInstance(object $instance, CacheItemPoolInterface $cacheItemPool): CacheProxy
    {
        // Create an anonymous instance of a class that extends CacheProxy and pass in the parameters to its constructor
        return new class($instance, $cacheItemPool) extends CacheProxy {
            public function __construct(mixed $instance, CacheItemPoolInterface $cacheItemPool)
            {
                parent::__construct($instance::class, $cacheItemPool);
                $this->instance = $instance;
            }
        };
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws ReflectionException
     */
    public function __call(string $name, array $arguments)
    {
        // create a cache key from the method name and arguments
        $cacheKey = $name . '_' . md5(serialize($arguments));

        // call the method on the instance and store its returned value in cache
        $method = $this->reflectionClass->getMethod($name);

        // check if the called method returns void
        $returnType      = $method->getReturnType();
        $isValueReturned = is_null($returnType) || $returnType->getName() !== 'void';

        if ($isValueReturned) {
            // create a hash of the property values of the instance
            $properties           = [];
            $reflectionProperties = $this->reflectionClass->getProperties();
            foreach ($reflectionProperties as $reflectionProperty) {
                if (!$reflectionProperty->isInitialized($this->getInstance())) {
                    continue;
                }

                if ($reflectionProperty->isStatic()) {
                    continue;
                }

                $properties[$reflectionProperty->getName()] = $reflectionProperty->getValue($this->getInstance());
            }

            ksort($properties);
            $cacheKey .= '-' . md5(serialize($properties));
            $item     = $this->cacheItemPool->getItem($cacheKey);

            // check if the cache key is defined in the cache item pool
            if ($item->isHit()) {
                return $item->get();
            }
        }

        $result = $method->invokeArgs($this->getInstance(), $arguments) ?? null;
        if ($isValueReturned) {
            $item->set($result);
            $this->cacheItemPool->save($item);
        }

        return $result;
    }

    /**
     * @return TClassName
     * @throws ReflectionException
     */
    private function getInstance(): mixed
    {
        // create and return a new instance, if not already done so
        if ($this->instance === null) {
            $this->instance = $this->createInstance();
        }

        return $this->instance;
    }

    /**
     * @return TClassName
     * @throws ReflectionException
     */
    private function createInstance(): mixed
    {
        // Now create an instance of it without the constructor
        return $this->reflectionClass->newInstanceWithoutConstructor();
    }
}
