<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Style;

class Property
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $value = null,
    ) {}

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
