<?php

namespace Azure\Storage\Api\Util;

use Throwable;

/**
 * @template Return
 */
interface PromiseInterface
{
    /**
     * @param callable<callable<Return>, callable<Throwable>> $callback
     * @return $this
     */
    public function then(callable $callback): static;

    /**
     * @param callable<callable<Throwable>, ...> $callback
     * @return $this
     */
    public function catch(callable $callback): static;
}
