<?php

namespace Azure\Storage\Traits\Storage;

use Azure\Storage\Api\Storage\ItemInterface;

trait ItemAwareTrait
{
    private ItemInterface $item;

    final public function setItem(ItemInterface &$item): void
    {
        $this->item = &$item;
    }
}
