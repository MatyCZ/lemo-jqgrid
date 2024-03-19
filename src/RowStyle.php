<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

use Lemo\JqGrid\Style\Property;

class RowStyle
{
    /**
     * @var ColumnCondition[]|null
     */
    protected ?array $conditions = null;

    /**
     * @var Property[]|null
     */
    protected ?array $properties = null;

    public function addCondition(ColumnCondition $condition): self
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * @param ColumnCondition[]|null $conditions
     */
    public function setConditions(?array $conditions): self
    {
        if (null === $conditions) {
            $this->conditions = null;

            return $this;
        }

        foreach ($conditions as $condition) {
            $this->addCondition($condition);
        }

        return $this;
    }

    /**
     * @return ColumnCondition[]|null
     */
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    public function addProperty(Property $property): self
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * Set styles for a column.
     */
    public function setProperties(?array $properties): self
    {
        if (null === $properties) {
            $this->properties = null;

            return $this;
        }

        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * @return Property[]|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }
}
