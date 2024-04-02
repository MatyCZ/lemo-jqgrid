<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

interface ColumnInterface
{
    /**
     * Retrieve attributes for a column
     */
    public function getAttributes(): ?ColumnAttributes;

    /**
     * Set the column identifier
     */
    public function setIdentifier(?string $identifier): self;

    /**
     * Retrieve the column identifier
     */
    public function getIdentifier(): ?string;

    /**
     * Retrieve the column name
     */
    public function getName(): ?string;

    /**
     * Set conditions for a column
     *
     * @param  ColumnCondition[]|null $conditions
     */
    public function setConditions(?array $conditions): self;

    /**
     * Retrieve conditions for a column
     *
     * @return ColumnCondition[]|null
     */
    public function getConditions(): ?array;

    /**
     * Set the value of the column
     */
    public function setValue(mixed $value): self;

    /**
     * Retrieve the column value
     */
    public function getValue(): mixed;

    public function isValid(AdapterInterface $adapter, array $rowData): bool;

    public function renderValue(AdapterInterface $adapter, array $rowData): float|int|string|null;
}
