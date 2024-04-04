<?php

namespace Azure\Storage\Api\Http\Request;

enum ResourceRequestTypeEnum: string
{
    case METADATA   = 'metadata';
    case LIST       = 'list';
    case ACL        = 'acl';
    case PROPERTIES = '';
}
