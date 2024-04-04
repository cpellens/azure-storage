<?php

namespace Azure\Storage\Library\Defaults\Http;

enum MethodEnum: string
{

    case GET     = 'GET';
    case POST    = 'POST';
    case PUT     = 'PUT';
    case DELETE  = 'DELETE';
    case HEAD    = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case PATCH   = 'PATCH';
    case CONNECT = 'CONNECT';
    case TRACE   = 'TRACE';
}
