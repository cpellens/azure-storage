<?php

namespace Azure\Storage\Library\Storage\Cache;

use ArrayAccess;
use ArrayObject;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ArrayAdapter implements CacheItemPoolInterface
{
    public function __construct(private ArrayAccess $arrayAccess = new ArrayObject())
    {
    }

    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            if ($this->hasItem($key)) {
                yield $key => $this->getItem($key);
            }
        }
    }

    public function hasItem(string $key): bool
    {
        return isset($this->arrayAccess[$key]);
    }

    public function getItem($key): CacheItemInterface
    {
        if (!$this->hasItem($key)) {
            $this->arrayAccess[$key] = new CacheItem($key, null);
        }

        return $this->arrayAccess[$key];
    }

    public function clear(): bool
    {
        $this->arrayAccess = new ArrayObject();
        return true;
    }

    public function deleteItem(string $key): bool
    {
        unset($this->arrayAccess[$key]);
        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->arrayAccess[$key]);
        }

        return true;
    }

    public function saveDeferred(CacheItemInterface $cacheItem): bool
    {
        return $this->save($cacheItem);
    }

    public function save(CacheItemInterface $cacheItem): bool
    {
        $this->arrayAccess[$cacheItem->getKey()] = $cacheItem;
        return true;
    }

    public function commit(): bool
    {
        return true;
    }


}
