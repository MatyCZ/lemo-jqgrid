<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

interface AdapterInterface
{
    /**
     * Prepare adapter
     */
    public function prepare(Grid $grid): self;

    /**
     * Fetch data from adapter to platform resultset
     */
    public function fetchData(): self;

    /**
     * Find value for column
     */
    public function findValue(string $identifier, array $item, int $depth = 0): mixed;

    /**
     * Get number of current page
     */
    public function getNumberOfPages(): int;

    /**
     * Return count of items
     */
    public function getCountOfItems(): int;

    /**
     * Return count of items total
     */
    public function getCountOfItemsTotal(): int;
}
