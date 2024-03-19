<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Laminas\Stdlib\InitializableInterface;
use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;
use Lemo\JqGrid\ColumnCondition;
use Lemo\JqGrid\ColumnInterface;
use Lemo\JqGrid\ColumnPrepareAwareInterface;
use Lemo\JqGrid\Constant\OperatorConstant;
use Lemo\JqGrid\Exception;
use Lemo\JqGrid\Grid;

use function addslashes;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function sprintf;
use function str_starts_with;
use function strtolower;

abstract class AbstractColumn implements
    ColumnInterface,
    InitializableInterface,
    ColumnPrepareAwareInterface
{
    protected ColumnAttributes $attributes;

    /**
     * Standard boolean attributes, with expected values for enabling/disabling
     */
    protected array $booleanAttributes = [
        'autocomplete' => ['on' => 'on', 'off' => 'off'],
        'autofocus'    => ['on' => 'autofocus', 'off' => ''],
        'checked'      => ['on' => 'checked', 'off' => ''],
        'disabled'     => ['on' => 'disabled', 'off' => ''],
        'multiple'     => ['on' => 'multiple', 'off' => ''],
        'readonly'     => ['on' => 'readonly', 'off' => ''],
        'required'     => ['on' => 'required', 'off' => ''],
        'selected'     => ['on' => 'selected', 'off' => ''],
    ];

    /**
     * @var ColumnCondition[]|null
     */
    protected ?array $conditions = null;
    protected ?string $identifier = null;
    protected string $name;

    /**
     * Attributes globally valid for all tags
     *
     * @var array
     */
    protected array $validGlobalAttributes = [
        'accesskey'          => true,
        'class'              => true,
        'contenteditable'    => true,
        'contextmenu'        => true,
        'dir'                => true,
        'draggable'          => true,
        'dropzone'           => true,
        'hidden'             => true,
        'id'                 => true,
        'lang'               => true,
        'onabort'            => true,
        'onblur'             => true,
        'oncanplay'          => true,
        'oncanplaythrough'   => true,
        'onchange'           => true,
        'onclick'            => true,
        'oncontextmenu'      => true,
        'ondblclick'         => true,
        'ondrag'             => true,
        'ondragend'          => true,
        'ondragenter'        => true,
        'ondragleave'        => true,
        'ondragover'         => true,
        'ondragstart'        => true,
        'ondrop'             => true,
        'ondurationchange'   => true,
        'onemptied'          => true,
        'onended'            => true,
        'onerror'            => true,
        'onfocus'            => true,
        'oninput'            => true,
        'oninvalid'          => true,
        'onkeydown'          => true,
        'onkeypress'         => true,
        'onkeyup'            => true,
        'onload'             => true,
        'onloadeddata'       => true,
        'onloadedmetadata'   => true,
        'onloadstart'        => true,
        'onmousedown'        => true,
        'onmousemove'        => true,
        'onmouseout'         => true,
        'onmouseover'        => true,
        'onmouseup'          => true,
        'onmousewheel'       => true,
        'onpause'            => true,
        'onplay'             => true,
        'onplaying'          => true,
        'onprogress'         => true,
        'onratechange'       => true,
        'onreadystatechange' => true,
        'onreset'            => true,
        'onscroll'           => true,
        'onseeked'           => true,
        'onseeking'          => true,
        'onselect'           => true,
        'onshow'             => true,
        'onstalled'          => true,
        'onsubmit'           => true,
        'onsuspend'          => true,
        'ontimeupdate'       => true,
        'onvolumechange'     => true,
        'onwaiting'          => true,
        'spellcheck'         => true,
        'style'              => true,
        'tabindex'           => true,
        'title'              => true,
        'xml:base'           => true,
        'xml:lang'           => true,
        'xml:space'          => true,
    ];

    /**
     * Attributes valid for the tag represented by this helper
     *
     * This should be overridden in extending classes
     */
    protected array $validTagAttributes = [];
    protected mixed $value = null;

    public function __construct(string $name, ?string $identifier = null)
    {
        $this->attributes = new ColumnAttributes();

        $this->identifier = $identifier ?? $name;
        $this->name = $name;
    }

    /**
     * This function is automatically called when creating column with factory. It
     * allows to perform various operations (add columns...)
     */
    #[\Override]
    public function init(): void {}

    /**
     * Prepare the grid column (mostly used for rendering purposes)
     */
    #[\Override]
    public function prepareColumn(Grid $grid): self
    {
        $filters = $grid->getParam('filters');

        $this->init();

        $name = $this->getName();

        if (!empty($filters['rules'][$name])) {
            foreach ($filters['rules'][$name] as $filterDefinition) {
                $operator = $filterDefinition['operator'];
                $operatorOutput = $grid->getFilterOperatorOutput($operator);

                $value = (string) $filterDefinition['value'];

                $this->getAttributes()->setSearchDataInit("function(elem) {
                    $(elem).val('" . addslashes($value) . "');
                    $(elem).parents('tr').find(\"[colname='{$name}']\").attr('soper', '{$operatorOutput}').text('{$operator}');
                }");
            }
        }

        return $this;
    }

    /**
     * Get column attributes
     */
    #[\Override]
    public function getAttributes(): ?ColumnAttributes
    {
        return $this->attributes;
    }

    public function addCondition(ColumnCondition $condition): self
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * Set conditions for a column.
     *
     * @param ColumnCondition[]|null $conditions
     */
    #[\Override]
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
     * Get defined conditions
     *
     * @return ColumnCondition[]|null
     */
    #[\Override]
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    /**
     * Set the column identifier
     */
    #[\Override]
    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get the column identifier
     */
    #[\Override]
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * Get the column name
     */
    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the column value
     */
    #[\Override]
    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the column value
     */
    #[\Override]
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    #[\Override]
    public function isValid(AdapterInterface $adapter, array $rowData): bool
    {
        $conditions = $this->getConditions();
        $isValid = true;

        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                $value = $adapter->findValue($condition->getColumn(), $rowData);

                switch ($condition->getOperator()) {
                    case OperatorConstant::OPERATOR_IN:
                        if (
                            !is_array($condition->getValue())
                            || !in_array($value, $condition->getValue())
                        ) {
                            $isValid = false;
                        }
                        break;
                    case OperatorConstant::OPERATOR_EQUAL:
                        if ($value != $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case OperatorConstant::OPERATOR_NOT_EQUAL:
                        if ($value == $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case OperatorConstant::OPERATOR_GREATER:
                        if ($value <= $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case OperatorConstant::OPERATOR_GREATER_OR_EQUAL:
                        if ($value < $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case OperatorConstant::OPERATOR_LESS:
                        if ($value >= $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case OperatorConstant::OPERATOR_LESS_OR_EQUAL:
                        if ($value > $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                }
            }
        }

        return $isValid;
    }

    /**
     * Prepare attributes for rendering
     *
     * Ensures appropriate attributes are present (e.g., if "name" is present,
     * but no "id", sets the latter to the former).
     *
     * Removes any invalid attributes
     */
    protected function prepareAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            $attribute = strtolower($key);

            if (!isset($this->validGlobalAttributes[$attribute])
                && !isset($this->validTagAttributes[$attribute])
                && !str_starts_with($attribute, 'data-')
            ) {
                // Invalid attribute for the current tag
                unset($attributes[$key]);
                continue;
            }

            // Normalize attribute key, if needed
            if ($attribute != $key) {
                unset($attributes[$key]);
                $attributes[$attribute] = $value;
            }

            // Normalize boolean attribute values
            if (isset($this->booleanAttributes[$attribute])) {
                $attributes[$attribute] = $this->prepareBooleanAttributeValue($attribute, $value);
            }
        }

        return $attributes;
    }

    /**
     * Prepare a boolean attribute value
     *
     * Prepares the expected representation for the boolean attribute specified.
     */
    protected function prepareBooleanAttributeValue(string $attribute, mixed $value): string
    {
        if (!is_bool($value) && in_array($value, $this->booleanAttributes[$attribute])) {
            return $value;
        }

        $value = (bool) $value;

        return $value
            ? $this->booleanAttributes[$attribute]['on']
            : $this->booleanAttributes[$attribute]['off']
        ;
    }

    /**
     * Create a string of all attribute/value pairs
     *
     * Escapes all attribute values
     */
    public function createAttributesString(array $attributes): string
    {
        $attributes = $this->prepareAttributes($attributes);

        $strings = [];
        foreach ($attributes as $key => $value) {
            $key = strtolower($key);

            // Skip boolean attributes that expect empty string as false value
            if (
                !$value
                && isset($this->booleanAttributes[$key])
                && '' === $this->booleanAttributes[$key]['off']
            ) {
                continue;
            }

            $strings[] = sprintf('%s="%s"', $key, $value);
        }

        return implode(' ', $strings);
    }
}
