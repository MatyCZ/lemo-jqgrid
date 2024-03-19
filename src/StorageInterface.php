<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

use ArrayIterator;

interface StorageInterface
{
    /**
     * Clears contents from storage
     */
    public function clear(string $gridName): self;

    /**
     * Returns true if and only if storage is empty
     */
    public function exists(string $gridName): bool;

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     */
    public function read(string $gridName): ArrayIterator;

    /**
     * Writes $contents to storage
     */
    public function write(string $gridName, ArrayIterator $params): self;
}
