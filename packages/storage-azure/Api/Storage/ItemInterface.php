<?php

namespace Azure\Storage\Api\Storage;

use ArrayAccess;
use DateTimeInterface;

interface ItemInterface extends ResourceInterface
{

    public function getName(): string;

    public function getCreatedAt(): DateTimeInterface;

    public function getUpdatedAt(): DateTimeInterface;

    public function getMetadata(): ArrayAccess;

    public function setMetadata(ArrayAccess $arrayAccess): void;

    public function getProperties(): ArrayAccess;
}
