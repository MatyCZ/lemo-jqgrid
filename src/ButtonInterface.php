<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

interface ButtonInterface
{
    public function isValid(AdapterInterface $adapter, array $rowData): bool;

    public function render(array $rowData): string;
}
