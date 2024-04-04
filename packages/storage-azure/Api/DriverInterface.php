<?php

namespace Azure\Storage\Api;

interface DriverInterface
{
    // Containers
    public function createContainer(string $name);

    public function readContainer(string $name);

    public function updateContainer(string $name);

    public function deleteContainer(string $name);

    // Blobs
    public function createBlob(string $containerName, string $blobName, string $blobData);

    public function readBlob(string $containerName, string $blobName);

    public function updateBlob(string $containerName, string $blobName, string $blobData);

    public function deleteBlob(string $containerName, string $blobName);
}
