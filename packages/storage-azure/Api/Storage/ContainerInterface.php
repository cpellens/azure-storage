<?php

namespace Azure\Storage\Api\Storage;

use SeekableIterator;

/**
 * Interface for blob containers to interact like a directory
 * @extends SeekableIterator<int, ItemInterface>
 */
interface ContainerInterface extends ItemInterface
{
    public function list(): iterable;

    public function get(string $itemName): ItemInterface;

    public function store(ItemInterface $item): void;

    public function isPublic(): bool;
}
