<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;

class Date extends AbstractColumn
{
    public function __construct(
        string $name,
        ?string $identifier = null
    ) {
        parent::__construct($name, $identifier);

        $this->getAttributes()->setFormat(ColumnAttributes::FORMAT_DATE);
        $this->getAttributes()->setFormatOptions([
            'srcformat' => 'Y-m-d',
            'newformat' => 'd.m.Y'
        ]);
        $this->getAttributes()->setSearchOperators(ColumnAttributes::SEARCH_OPERATORS_DATE);
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): ?string
    {
        return (string) $this->getValue();
    }
}
