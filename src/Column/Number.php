<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;

use function round;

class Number extends AbstractColumn
{
    protected ?int $multiplier = null;
    protected ?int $divisor = null;

    public function __construct(
        string $name,
        ?string $identifier = null
    ) {
        parent::__construct($name, $identifier);

        $this->getAttributes()->setSearchOperators(ColumnAttributes::SEARCH_OPERATORS_NUMBER);
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): string
    {
        $value = $this->getValue();

        if (null !== $this->getMultiplier()) {
            $value = round($value * $this->getMultiplier());
        }

        if (null !== $this->getDivisor()) {
            $value = round($value / $this->getDivisor());
        }

        return (string) $value;
    }

    public function setDivisor(?int $divisor): self
    {
        $this->divisor = $divisor;

        return $this;
    }

    public function getDivisor(): ?int
    {
        return $this->divisor;
    }

    public function setMultiplier(?int $multiplier): self
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    public function getMultiplier(): ?int
    {
        return $this->multiplier;
    }
}
