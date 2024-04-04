<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;

try {
    return RectorConfig::configure()
                       ->withPaths([
                           __DIR__ . '/packages',
                           __DIR__ . '/tests',
                       ])
                       ->withPhpSets()
                       ->withAttributesSets(symfony: true, doctrine: true, mongoDb: true)
                       ->withImportNames()
                       ->withPreparedSets(
                           true,
                           true,
                           true,
                           true,
                           true,
                           true,
                           true,
                           true,
                           true
                       );
} catch (InvalidConfigurationException $e) {
    echo $e->getMessage();
}
