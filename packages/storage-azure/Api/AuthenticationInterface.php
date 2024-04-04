<?php

namespace Azure\Storage\Api;

use Psr\Http\Message\RequestInterface;

interface AuthenticationInterface
{
    public function authenticate(RequestInterface &$request = null): void;
}
