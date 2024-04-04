<?php

namespace Azure\Storage\Api\Http\Response;

use Azure\Storage\Api\Storage\BlobInterface;
use Countable;
use Iterator;

/**
 * @extends Iterator<BlobInterface>
 */
interface ListBlobsResponseInterface extends Countable, Iterator
{
}
