<?php

namespace Azure\Storage\Traits\Storage;

use ArrayAccess;
use Psr\Http\Client\ClientExceptionInterface;

trait MetadataAwareTrait
{
    private ArrayAccess $metadata;

    /**
     * @throws ClientExceptionInterface
     */
    public function getMetadata(): ArrayAccess
    {
        if (!isset($this->metadata)) {
            $this->loadMetadata();
        }

        return $this->metadata;
    }

    abstract protected function loadMetadata(): void;
}
