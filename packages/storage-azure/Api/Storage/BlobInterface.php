<?php

namespace Azure\Storage\Api\Storage;

use Psr\Http\Message\StreamInterface;

interface BlobInterface extends ItemInterface
{
    public function getStream(): StreamInterface;

    public function getContentType(): string;

    public function setContentType(string $contentType): void;

    public function getVersion(): string;
}
