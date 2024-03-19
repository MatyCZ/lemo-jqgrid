<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;

use function array_keys;
use function array_values;
use function str_replace;

class Boolean extends AbstractColumn
{
    protected ?string $falseLabel = null;
    protected ?string $falseValue = null;
    protected bool $optionsReplaceValue = true;
    protected ?string $trueLabel = null;
    protected ?string $trueValue = null;

    public function __construct(
        string $name,
        ?string $identifier = null,
    ) {
        parent::__construct($name, $identifier);

        $this->getAttributes()->setSearchElement(ColumnAttributes::SEARCH_ELEMENT_SELECT);
        $this->getAttributes()->setSearchOperators(ColumnAttributes::SEARCH_OPERATORS_BOOLEAN);
    }

    #[\Override]
    public function init(): void
    {
        $this->getAttributes()->setSearchValue([
            '' => '-',
            $this->getOptionTrueValue() => $this->getOptionTrueLabel(),
            $this->getOptionFalseValue() => $this->getOptionFalseLabel()
        ]);

        parent::init();
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): ?string
    {
        $value = (string) $this->getValue();

        if (true !== $this->getOptionsReplaceValue()) {
            return $value;
        }

        return str_replace(
            array_keys([$this->getOptionFalseValue(), $this->getOptionTrueValue()]),
            array_values([$this->getOptionFalseLabel(), $this->getOptionTrueLabel()]),
            $value
        );
    }

    public function setOptionFalse(?string $value, ?string $label): void
    {
        $this->falseLabel = $label;
        $this->falseValue = $value;
    }

    public function getOptionFalseLabel(): ?string
    {
        return $this->falseLabel;
    }

    public function getOptionFalseValue(): ?string
    {
        return $this->falseValue;
    }

    public function setOptionTrue(?string $value, ?string $label): void
    {
        $this->trueLabel = $label;
        $this->trueValue = $value;
    }

    public function getOptionTrueLabel(): ?string
    {
        return $this->trueLabel;
    }
    public function getOptionTrueValue(): ?string
    {
        return $this->trueValue;
    }

    public function setOptionsReplaceValue(bool $optionsReplaceValue): self
    {
        $this->optionsReplaceValue = $optionsReplaceValue;

        return $this;
    }

    public function getOptionsReplaceValue(): bool
    {
        return $this->optionsReplaceValue;
    }
}
