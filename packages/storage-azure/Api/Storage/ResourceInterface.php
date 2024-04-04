<?php

namespace Azure\Storage\Api\Storage;

interface ResourceInterface
{
    public function getId(): string;

    public function getResourceType(): ResourceTypeEnum;
}
