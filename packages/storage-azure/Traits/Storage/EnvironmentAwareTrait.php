<?php

namespace Azure\Storage\Traits\Storage;

use Azure\Storage\Api\EnvironmentInterface;

trait EnvironmentAwareTrait
{
    private EnvironmentInterface $environment;

    public function getEnvironment(): EnvironmentInterface
    {
        return $this->environment;
    }

    public function setEnvironment(EnvironmentInterface $environment): void
    {
        $this->environment = $environment;
    }
}
