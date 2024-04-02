<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;

class DateTime extends AbstractColumn
{
    protected bool $showSeconds = true;

    public function __construct(
        string $name,
        ?string $identifier = null
    ) {
        parent::__construct($name, $identifier);

        $newFormat = 'd.m.Y - H:i';
        if ($this->getShowSeconds()) {
            $newFormat = 'd.m.Y - H:i:s';
        }

        $this->getAttributes()->setFormat(ColumnAttributes::FORMAT_DATE);
        $this->getAttributes()->setFormatOptions([
            'srcformat' => 'Y-m-d H:i:s',
            'newformat' => $newFormat
        ]);
        $this->getAttributes()->setSearchOperators(ColumnAttributes::SEARCH_OPERATORS_DATE);
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): ?string
    {
        return (string) $this->getValue();
    }

    public function setShowSeconds(bool $showSeconds): self
    {
        $this->showSeconds = $showSeconds;

        return $this;
    }

    public function getShowSeconds(): bool
    {
        return $this->showSeconds;
    }
}
