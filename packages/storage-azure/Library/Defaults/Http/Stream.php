<?php

namespace Azure\Storage\Library\Defaults\Http;

use Psr\Http\Message\StreamInterface;
use Stringable;

class Stream implements StreamInterface, Stringable
{
    /**
     * @param resource|closed-resource $resource
     */
    public function __construct(private mixed $resource)
    {
        if (is_null($this->resource)) {
            $this->resource = fopen('php://memory', 'r+');
        }
    }

    public function __toString(): string
    {
        return $this->getContents();
    }

    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            return '';
        }
        return (string)stream_get_contents($this->resource);
    }

    public function close(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    /**
     * @return resource|closed-resource
     */
    public function detach()
    {
        $resourceHandle = $this->resource;
        unset($this->resource);

        return $resourceHandle;
    }

    public function getSize(): int
    {
        if (!is_resource($this->resource)) {
            return -1;
        }

        // get the length of the resource contents
        $stat = fstat($this->resource);
        if ($stat === [] || $stat === false) {
            return -1;
        }

        return $stat['size'];
    }

    public function tell(): int
    {
        return ftell($this->resource);
    }

    public function eof(): bool
    {
        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        // return whether the resource is seekable
        return is_resource($this->resource) && $this->getMetadata('seekable');
    }

    public function getMetadata(?string $key = null)
    {
        $meta = stream_get_meta_data($this->resource);
        return $key !== null && $key !== '' && $key !== '0' ? $meta[$key] : $meta;
    }

    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return fseek($this->resource, $offset, $whence);
    }

    public function rewind(): bool
    {
        return rewind($this->resource);
    }

    public function isWritable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $fileMode = $this->getMetadata('mode');

        // return true if the fileMode is one of the writable types
        return str_starts_with((string)$fileMode, 'w') || str_starts_with((string)$fileMode, 'a')
            || str_starts_with((string)$fileMode, 'x') || str_starts_with((string)$fileMode, 'c')
            || str_ends_with((string)$fileMode, '+');
    }

    public function write(string $string): int|false
    {
        return fwrite($this->resource, $string);
    }

    public function isReadable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $fileMode = $this->getMetadata('mode');

        // return true if the fileMode is one of the readable types
        return str_starts_with((string)$fileMode, 'r') || str_ends_with((string)$fileMode, '+');
    }

    public function read(int $length): false|string
    {
        return fread($this->resource, $length);
    }

}
