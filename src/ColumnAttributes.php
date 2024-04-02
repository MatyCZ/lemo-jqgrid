<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

use Exception;
use Laminas\Stdlib\AbstractOptions;

use function in_array;
use function strtolower;

class ColumnAttributes extends AbstractOptions
{
    final public const ALIGN_CENTER = 'center';
    final public const ALIGN_LEFT = 'left';
    final public const ALIGN_RIGHT = 'right';
    final public const FORMAT_CURRENCY = 'currency';
    final public const FORMAT_DATE = 'date';
    final public const SEARCH_ELEMENT_SELECT = 'select';
    final public const SEARCH_ELEMENT_TEXT = 'text';
    final public const SEARCH_GROUPOPERATOR_AND = 'and';
    final public const SEARCH_GROUPOPERATOR_OR = 'or';
    final public const SEARCH_OPERATORS_BOOLEAN = ['eq'];
    final public const SEARCH_OPERATORS_DATE = ['cn', 'nc', 'eq', 'ne', 'lt', 'le', 'gt', 'ge', 'bw', 'bn', 'ew', 'en'];
    final public const SEARCH_OPERATORS_NUMBER = ['cn', 'nc', 'eq', 'ne', 'lt', 'le', 'gt', 'ge'];
    final public const SEARCH_OPERATORS_OPTIONS = ['eq', 'ne'];
    final public const SEARCH_OPERATORS_TEXT = ['cn', 'nc', 'eq', 'ne', 'bw', 'bn', 'ew', 'en'];
    final public const SEARCH_TYPE_HAVING = 'having';
    final public const SEARCH_TYPE_WHERE = 'where';
    final public const SORT_ORDER_ASC = 'asc';
    final public const SORT_ORDER_DESC = 'desc';

    /**
     * Defines the alignment of the cell in the Body layer, not in header cell. Possible values: left, center, right.
     */
    protected ?string $align = null;

    /**
     * This function add attributes to the cell during the creation of the data - i.e dynamically. By example all valid
     * attributes for the table cell can be used or a style attribute with different properties. The function should
     * return string. Parameters passed to this function are:
     * - rowId - the id of the row
     * - val - the value which will be added in the cell
     * - rawObject - the raw object of the data row - i.e if datatype is json - array, if datatype is xml xml node.
     * - cm - all the properties of this column listed in the colModel
     * - rdata - the data row which will be inserted in the row. This parameter is array of type name:value, where name is the name in colModel
     */
    protected ?array $columnAttributes = null;

    /**
     * This option allow to add classes to the column. If more than one class will be used a space should be set.
     * By example classes:'class1 class2' will set a class1 and class2 to every cell on that column. In the grid css
     * there is a predefined class ui-ellipsis which allow to attach ellipsis to a particular row. Also this will work
     * in FireFox too.
     */
    protected ?string $class = null;

    /**
     * Governs format of sorttype:date (when datetype is set to local) and editrules {date:true} fields. Determines the
     * expected date format for that column. Uses a PHP-like date formatting. Currently ”/”, ”-”, and ”.” are supported
     * as date separators. Valid formats are:
     * - y,Y,yyyy for four digits year
     * - YY, yy for two digits year
     * - m,mm for months
     * - d,dd for days.
     */
    protected ?string $dateFormat = null;

    /**
     * Defines the edit type for inline and form editing Possible values: text, textarea, select, checkbox, password,
     * button, image and file.
     */
    protected ?string $editElement = null;

    /**
     * Defines various options for form editing.
     */
    protected ?array $editElementOptions = null;

    /**
     * Array of allowed options (attributes) for edittype option.
     */
    protected ?array $editOptions = null;

    /**
     * Sets additional rules for the editable column.
     */
    protected ?array $editRules = null;

    /**
     * The predefined types (string) or custom function name that controls the format of this field.
     *
     * @var mixed
     */
    protected mixed $format = null;

    /**
     * Format options can be defined for particular columns, overwriting the defaults from the language file.
     */
    protected ?array $formatOptions = null;

    /**
     * Set the index name when sorting. Passed as sidx parameter.
     */
    protected ?string $identifier = null;

    /**
     * Defines if the field is editable. This option is used in cell, inline and form modules.
     */
    protected ?bool $isEditable = null;

    /**
     * If set to true this option does not allow recalculation of the width of the column if shrinkToFit option is set
     * to true. Also the width does not change if a setGridWidth method is used to change the grid width.
     */
    protected ?bool $isFixed = null;

    /**
     * If set to true determines that this column will be frozen after calling the setFrozenColumns method.
     */
    protected ?bool $isFrozen = null;

    /**
     * Defines if this column is hidden at initialization.
     */
    protected ?bool $isHidden = null;

    /**
     * If set to true this column will not appear in the modal dialog where users can choose which columns to show
     * or hide.
     */
    protected ?bool $isHideable = null;

    /**
     * Defines if the column can be re sized.
     */
    protected bool $isResizable = true;

    /**
     * When used in search modules, disables or enables searching on that column.
     */
    protected bool $isSearchable = true;

    /**
     * Defines is this can be sorted.
     */
    protected bool $isSortable = true;

    /**
     * When colNames array is empty, defines the heading for this column. If both the colNames array and this setting
     * are empty, the heading for this column comes from the name property.
     */
    protected ?string $label = null;

    protected ?string $name = null;

    /**
     * Determines the type of the element when searching. Possible values: text and select.
     */
    protected string $searchElement = self::SEARCH_ELEMENT_TEXT;

    /**
     * Determines the group operator of the element when searching. Possible values: and/or.
     */
    protected string $searchGroupOperator = self::SEARCH_GROUPOPERATOR_AND;

    /**
     * Defines the search options used searching.
     */
    protected array $searchOptions = [
        'sopt' => null,
        'value' => null,
    ];

    /**
     * Valid only in Custom Searching and edittype : 'select' and describes the url from where we can get
     * already-constructed select element.
     */
    protected ?string $searchUrl = null;

    /**
     * Determines the type of the element when searching. Possible values: text and select.
     */
    protected string $searchType = self::SEARCH_TYPE_WHERE;

    /**
     * If this option is false the title is not displayed in that column when we hover a cell with the mouse
     */
    protected bool $showTitle = true;

    /**
     * Used when datatype is local. Defines the type of the column for appropriate sorting. Possible values:
     * - int/integer - for sorting integer
     * - float/number/currency - for sorting decimal numbers
     * - date - for sorting date
     * - text - for text sorting
     * - function - defines a custom function for sorting.
     *
     * To this function we pass the value to be sorted and it should return a value too.
     */
    protected ?string $sortType = null;

    /**
     * This option acts as template which can be used in the summary footer row.
     *
     * By default its value is defined as {0} - which means that this will print the summary value. The parameter can
     * contain any valid HTML code.
     */
    protected ?string $summaryTpl = null;

    /**
     * The option determines what type of calculation we should do with the current group value applied to column.
     *
     * Currently we support the following build in functions:
     *   sum - apply the sum function to the current group value and return the result
     *   count - apply the count function to the current group value and return the result
     *   avg - apply the average function to the current group value and return the result
     *   min - apply the min function to the current group value and return the result
     *   max - apply the max function to the current group value and return the result
     */
    protected ?string $summaryType = null;

    /**
     * Set the initial width of the column, in pixels. This value currently can not be set as percentage.
     */
    protected ?int $width = null;

    public function setAlign(?string $align): self
    {
        $this->align = $align;

        return $this;
    }

    public function getAlign(): ?string
    {
        return $this->align;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setDateFormat(?string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function setEditElement(?string $editElement): self
    {
        $this->editElement = $editElement;

        return $this;
    }

    public function getEditElement(): ?string
    {
        return $this->editElement;
    }

    public function setEditElementOptions(?array $editElementOptions): self
    {
        $this->editElementOptions = $editElementOptions;

        return $this;
    }

    public function getEditElementOptions(): ?array
    {
        return $this->editElementOptions;
    }

    public function setEditOptions(?array $editOptions): self
    {
        $this->editOptions = $editOptions;

        return $this;
    }

    public function getEditOptions(): ?array
    {
        return $this->editOptions;
    }

    public function setEditRules(?array $editRules): self
    {
        $this->editRules = $editRules;

        return $this;
    }

    public function getEditRules(): ?array
    {
        return $this->editRules;
    }

    public function setFormat(mixed $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getFormat(): mixed
    {
        return $this->format;
    }

    public function setFormatOptions(?array $formatOptions): self
    {
        $this->formatOptions = $formatOptions;

        return $this;
    }

    public function getFormatOptions(): ?array
    {
        return $this->formatOptions;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIsEditable(?bool $isEditable): self
    {
        $this->isEditable = $isEditable;

        return $this;
    }

    public function getIsEditable(): ?bool
    {
        return $this->isEditable;
    }

    public function setIsFixed(?bool $isFixed): self
    {
        $this->isFixed = $isFixed;

        return $this;
    }

    public function getIsFixed(): ?bool
    {
        return $this->isFixed;
    }

    public function setIsFrozen(?bool $isFrozen): self
    {
        $this->isFrozen = $isFrozen;

        return $this;
    }

    public function getIsFrozen(): ?bool
    {
        return $this->isFrozen;
    }

    public function setIsHidden(?bool $isHidden): self
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    public function getIsHidden(): ?bool
    {
        return $this->isHidden;
    }

    public function setIsResizable(bool $isResizable): self
    {
        $this->isResizable = $isResizable;

        return $this;
    }

    public function getIsResizable(): bool
    {
        return $this->isResizable;
    }

    public function setIsHideable(?bool $isHideable): self
    {
        $this->isHideable = $isHideable;
        return $this;
    }

    public function getIsHideable(): ?bool
    {
        return $this->isHideable;
    }

    public function setIsSearchable(bool $isSearchable): self
    {
        $this->isSearchable = $isSearchable;

        return $this;
    }

    public function getIsSearchable(): bool
    {
        return $this->isSearchable;
    }

    public function setIsSortable(bool $isSortable): self
    {
        $this->isSortable = $isSortable;

        return $this;
    }

    public function getIsSortable(): bool
    {
        return $this->isSortable;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setSearchElement(string $searchElement): self
    {
        $this->searchElement = $searchElement;

        return $this;
    }

    public function getSearchElement(): string
    {
        return $this->searchElement;
    }

    public function setSearchUrl(?string $searchUrl): self
    {
        $this->searchUrl = $searchUrl;

        return $this;
    }

    public function getSearchUrl(): ?string
    {
        return $this->searchUrl;
    }

    /**
     * Set a default value in the search input element.
     */
    public function setSearchDataInit(mixed $dataInit): self
    {
        $this->searchOptions['dataInit'] = $dataInit;

        return $this;
    }

    /**
     * Get a default value in the search input element.
     */
    public function getSearchDataInit(): mixed
    {
        return $this->searchOptions['dataInit'];
    }

    /**
     * Set a default value in the search input element.
     */
    public function setSearchDefaultValue(mixed $defaultValue): self
    {
        $this->searchOptions['defaultValue'] = $defaultValue;

        return $this;
    }

    /**
     * Get a default value in the search input element.
     */
    public function getSearchDefaultValue(): mixed
    {
        return $this->searchOptions['defaultValue'];
    }

    /**
     * Set search grouping operator
     *
     * @throws Exception
     */
    public function setSearchGroupOperator(string $operator): self
    {
        $operator = strtolower($operator);

        if (!in_array($operator, ['and', 'or'])) {
            throw new Exception("Allowed search group operator is 'and' and 'or'");
        }

        $this->searchGroupOperator = $operator;

        return $this;
    }

    /**
     * Get search grouping operator.
     */
    public function getSearchGroupOperator(): string
    {
        return $this->searchGroupOperator;
    }

    /**
     * Set search input element operators.
     */
    public function setSearchOperators(array $operator): self
    {
        $this->searchOptions['sopt'] = $operator;

        return $this;
    }

    /**
     * Get search input element operators.
     */
    public function getSearchOperators(): array
    {
        return $this->searchOptions['sopt'];
    }

    public function setSearchOptions(array $searchOptions): self
    {
        $this->searchOptions = $searchOptions;

        return $this;
    }

    public function getSearchOptions(): array
    {
        return $this->searchOptions;
    }

    /**
     * Set a default value in the search input element.
     */
    public function setSearchValue(mixed $value): self
    {
        $this->searchOptions['value'] = $value;
        return $this;
    }

    /**
     * Get a default value in the search input element.
     */
    public function getSearchValue(): mixed
    {
        return $this->searchOptions['value'];
    }

    /**
     * Set search type
     *
     * @throws Exception
     */
    public function setSearchType(string $type): self
    {
        $type = strtolower($type);

        if (!in_array($type, ['where', 'having'])) {
            throw new Exception("Allowed search types are 'where' and 'having'");
        }

        $this->searchType = $type;

        return $this;
    }

    public function getSearchType(): string
    {
        return $this->searchType;
    }

    public function setShowTitle(bool $showTitle): self
    {
        $this->showTitle = $showTitle;
        return $this;
    }

    public function getShowTitle(): bool
    {
        return $this->showTitle;
    }

    public function setSortType(?string $sortType): self
    {
        $this->sortType = $sortType;

        return $this;
    }

    public function getSortType(): ?string
    {
        return $this->sortType;
    }

    public function setSummaryTpl(?string $summaryTpl): self
    {
        $this->summaryTpl = $summaryTpl;

        return $this;
    }

    public function getSummaryTpl(): ?string
    {
        return $this->summaryTpl;
    }

    public function setSummaryType(?string $summaryType): self
    {
        $this->summaryType = $summaryType;

        return $this;
    }

    public function getSummaryType(): ?string
    {
        return $this->summaryType;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }
}
