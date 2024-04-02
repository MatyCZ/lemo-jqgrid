<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

interface ColumnPrepareAwareInterface
{
    /**
     * Prepare the grid column (mostly used for rendering purposes)
     */
    public function prepareColumn(Grid $grid): self;
}
