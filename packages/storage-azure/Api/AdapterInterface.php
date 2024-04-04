<?php

namespace Azure\Storage\Api;

use Azure\Storage\Api\Storage\ContainerInterface;
use Azure\Storage\Api\Storage\ItemInterface;

interface AdapterInterface
{

    public function getEnvironment(): EnvironmentInterface;

    public function getContainer(string $containerName): ContainerInterface;

    public function store(ItemInterface $item): void;
}
