<?php

namespace Azure\Storage\Library\Defaults\Http\Response;

use Azure\Storage\Api\Http\Response\ListBlobsResponseInterface;
use Azure\Storage\Api\Storage\BlobInterface;

class ListBlobsResponse implements ListBlobsResponseInterface
{
    public const string STR_ROOT_TAG = 'EnumerationResults';

    protected string $prefix;
    protected string $marker;
    protected int    $maxResults;
    protected string $delimiter;

    protected array $blobs;

    public function current(): BlobInterface
    {
        return $this->blobs[0];
    }

    public function next(): void
    {
        // TODO: Implement next() method.
    }

    public function key(): string
    {
        return '';
    }

    public function valid(): bool
    {
        return false;
    }

    public function rewind(): void
    {
        // TODO: Implement rewind() method.
    }

    public function count(): int
    {
        return 0;
    }
}
