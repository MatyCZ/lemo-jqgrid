<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;

use function array_keys;
use function array_values;
use function str_replace;

class Text extends AbstractColumn
{
    protected ?array $textToReplace = null;

    public function __construct(
        string $name,
        ?string $identifier = null
    ) {
        parent::__construct($name, $identifier);

        $this->getAttributes()->setSearchOperators(ColumnAttributes::SEARCH_OPERATORS_TEXT);
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): ?string
    {
        $textToReplace = $this->getTextToReplace();

        $value = (string) $this->getValue();

        if (null === $textToReplace) {
            return $value;
        }

        return str_replace(
            array_keys($textToReplace),
            array_values($textToReplace),
            $value
        );
    }

    public function setTextToReplace(?array $textToReplace): self
    {
        $this->textToReplace = $textToReplace;

        return $this;
    }

    public function getTextToReplace(): ?array
    {
        return $this->textToReplace;
    }
}
