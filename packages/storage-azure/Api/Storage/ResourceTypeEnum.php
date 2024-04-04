<?php

namespace Azure\Storage\Api\Storage;

enum ResourceTypeEnum: string
{
    case CONTAINER = 'container';
    case BLOB      = 'blob';
}
