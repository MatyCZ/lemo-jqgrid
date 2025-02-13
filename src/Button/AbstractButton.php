<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Button;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ButtonInterface;
use Lemo\JqGrid\ColumnCondition;
use Lemo\JqGrid\Constant\OperatorConstant;
use Lemo\JqGrid\Exception;

use function array_key_exists;
use function implode;
use function in_array;
use function is_array;
use function sprintf;
use function str_starts_with;
use function strtolower;

abstract class AbstractButton implements ButtonInterface
{
    /** @var array<string, bool|int|float|string|null>  */
    protected array $attributes = [];

    /**
     * @var ColumnCondition[]|null
     */
    protected ?array $conditions = null;

    protected string $content = '';

    /**
     * Attributes globally valid for all tags
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

    /**
     * @param bool|int|float|string|null $value
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @return bool|int|float|string|null
     */
    public function getAttribute(string $key)
    {
        if (!isset($this->attributes[$key])) {
            return null;
        }

        return $this->attributes[$key];
    }

    public function removeAttribute(string $key): self
    {
        unset($this->attributes[$key]);

        return $this;
    }

    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param iterable<string, bool|int|float|string|null> $arrayOrTraversable
     */
    public function setAttributes(iterable $arrayOrTraversable): self
    {
        foreach ($arrayOrTraversable as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @return array<string, bool|int|float|string|null>
     */
    public function getAttributes(): array
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
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
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

            if (
                !isset($this->validGlobalAttributes[$attribute])
                && !isset($this->validTagAttributes[$attribute])
                && !str_starts_with($attribute, 'data-')
            ) {
                // Invalid attribute for the current tag
                unset($attributes[$key]);
                continue;
            }

            // Normalize the attribute key, if needed
            if ($attribute != $key) {
                unset($attributes[$key]);
                $attributes[$attribute] = $value;
            }
        }

        return $attributes;
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

            $strings[] = sprintf(
                '%s="%s"',
                $key,
                $value
            );
        }

        return implode(' ', $strings);
    }
}
