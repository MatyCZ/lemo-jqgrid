<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

class GridResultSet
{
    protected ?array $data = null;
    protected ?array $dataUser = null;

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setDataUser(?array $dataUser): self
    {
        $this->dataUser = $dataUser;

        return $this;
    }

    public function getDataUser(): ?array
    {
        return $this->dataUser;
    }
}
