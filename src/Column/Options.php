<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;
use Lemo\JqGrid\Exception;

use function array_keys;
use function array_values;
use function str_replace;

class Options extends AbstractColumn
{
    protected ?array $options = null;
    protected bool $optionsReplaceValue = true;

    public function __construct(
        string $name,
        ?string $identifier = null
    ) {
        parent::__construct($name, $identifier);

        $this->getAttributes()->setSearchElement(ColumnAttributes::SEARCH_ELEMENT_SELECT);
        $this->getAttributes()->setSearchOperators(ColumnAttributes::SEARCH_OPERATORS_OPTIONS);
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): ?string
    {
        $options = $this->getOptions();

        $value = (string) $this->getValue();

        if (true !== $this->getOptionsReplaceValue()) {
            return $value;
        }

        return str_replace(
            array_keys($options),
            array_values($options),
            $value
        );
    }

    #[\Override]
    public function init(): void
    {
        parent::init();

        if (null === $this->getOptions()) {
            throw new Exception\RuntimeException(
                'Options for column "' . $this->getName() . '" are not set.'
            );
        }

        $this->getAttributes()->setSearchValue(['' => '-'] + $this->getOptions());
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
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
