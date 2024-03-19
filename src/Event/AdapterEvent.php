<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Event;

use Laminas\EventManager\Event;
use Lemo\JqGrid\Grid;

class AdapterEvent extends Event
{
    /**
     * List of events
     */
    final public const EVENT_FETCH_DATA  = 'lemoGrid.adapter.loadData';

    protected ?Grid $grid = null;

    public function setGrid(?Grid $grid): self
    {
        $this->grid = $grid;

        return $this;
    }

    public function getGrid(): ?Grid
    {
        return $this->grid;
    }
}
