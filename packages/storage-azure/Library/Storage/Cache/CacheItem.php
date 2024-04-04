<?php

namespace Azure\Storage\Library\Storage\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    public const int INT_DURATION_OFFSET = 60;

    private ?DateTimeInterface $expiresAt = null;

    public function __construct(private readonly string $key, private mixed $value)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value ?? null;
    }

    public function isHit(): bool
    {
        $dateTime = new DateTime('now');

        if (!($this->expiresAt ?? null) instanceof DateTimeInterface) {
            return false;
        }

        return $this->expiresAt > $dateTime;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        $this->expiresAt = $expiration;
        return $this;
    }

    public function expiresAfter(DateInterval|int|null $time): static
    {
        $dateTime             = new DateTime('now');
        $this->expiresAt = $time instanceof DateInterval
            ? $dateTime->add($time)
            : $dateTime->modify(sprintf('+%s seconds', $time));

        return $this;
    }
}
