<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

class ConcatOptions
{
    protected ?array $identifiers = null;
    protected ?string $pattern = null;
    protected ?string $separator = null;

    public function setIdentifiers(?array $identifiers): self
    {
        $this->identifiers = $identifiers;

        return $this;
    }

    public function getIdentifiers(): ?array
    {
        return $this->identifiers;
    }

    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setSeparator(?string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function getSeparator(): ?string
    {
        return $this->separator;
    }
}
