<?php

namespace Azure\Storage\Library\Defaults\Http\Request;

use Azure\Storage\Api\EnvironmentInterface;
use Azure\Storage\Api\Storage\ItemInterface;

class CreateContainerRequest extends PutResourceRequest
{
    public function __construct(
        EnvironmentInterface $environment,
        ItemInterface $item
    ) {
        parent::__construct($environment, $item);

        $this->setApiVersion('2011-08-18');
    }
}
