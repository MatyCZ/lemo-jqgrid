<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

class ColumnCondition
{
    protected ?string $column = null;
    protected ?string $operator = null;
    protected mixed $value = null;

    public function setColumn(?string $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function getColumn(): ?string
    {
        return $this->column;
    }

    public function setOperator(?string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
