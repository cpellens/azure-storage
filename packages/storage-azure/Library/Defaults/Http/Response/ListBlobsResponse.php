<?php

namespace Azure\Storage\Library\Defaults\Http\Response;

use Azure\Storage\Api\Http\Response\ListBlobsResponseInterface;
use Azure\Storage\Api\Storage\BlobInterface;

class ListBlobsResponse implements ListBlobsResponseInterface
{
    public const STR_ROOT_TAG = 'EnumerationResults';

    protected string $prefix;
    protected string $marker;
    protected int    $maxResults;
    protected string $delimiter;

    protected array $blobs;

    public function current(): BlobInterface
    {
        // TODO: Implement current() method.
    }

    public function next(): void
    {
        // TODO: Implement next() method.
    }

    public function key(): mixed
    {
        // TODO: Implement key() method.
    }

    public function valid(): bool
    {
        // TODO: Implement valid() method.
    }

    public function rewind(): void
    {
        // TODO: Implement rewind() method.
    }

    public function count(): int
    {
        // TODO: Implement count() method.
    }
}
