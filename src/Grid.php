<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Lemo\JqGrid\Constant\OperatorConstant;

use function array_key_exists;
use function count;
use function explode;
use function in_array;
use function is_array;
use function json_decode;
use function sprintf;
use function strpos;
use function strtolower;
use function trim;

class Grid implements EventManagerAwareInterface
{
    protected ?AdapterInterface $adapter = null;
    protected ?array $buttons = null;

    /**
     * @var array<string, ColumnInterface>
     */
    protected array $columns = [];

    /**
     * @var ColumnStyle[]|null
     */
    protected ?array $columnStyles = null;
    protected ?EventManagerInterface $eventManager = null;
    protected bool $isPrepared = false;
    protected bool $isRendered = false;
    protected ?string $name = null;
    protected GridOptions $options;
    protected array $queryParams = [];
    protected GridResultSet $resultSet;
    protected ?StorageInterface $storage = null;

    /**
     * @var RowStyle[]|null
     */
    protected ?array $rowStyles = null;

    public function __construct()
    {
        $this->options = new GridOptions();
        $this->resultSet = new GridResultSet();
    }

    public function init(): void {}

    /**
     * Sets the grid adapter
     */
    public function setAdapter(?AdapterInterface $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns the grid adapter
     */
    public function getAdapter(): ?AdapterInterface
    {
        $this->adapter->setGrid($this);

        return $this->adapter;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the grid name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get grid options
     */
    public function getOptions(): GridOptions
    {
        return $this->options;
    }

    public function setQueryParams(array $queryParams): self
    {
        $this->queryParams = $queryParams;

        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * Get class of platform resultset
     */
    public function getResultSet(): GridResultSet
    {
        return $this->resultSet;
    }

    public function setStorage(?StorageInterface $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Returns the persistent storage handler
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function isRequestForCurrentGrid(): bool
    {
        $this->checkIfIsPrepared();

        $queryName = $this->queryParams['_name'] ?? null;

        // Maji se nacist data pro jiny grid
        if ($queryName !== $this->getName()) {
            return false;
        }

        return true;
    }

    public function isPrepared(): bool
    {
        return $this->isPrepared;
    }

    /**
     * Check if is prepared
     */
    public function checkIfIsPrepared(): bool
    {
        if (true !== $this->isPrepared()) {
            throw new Exception\RuntimeException(
                sprintf(
                    "Grid '%s' is not prepared.",
                    $this->getName()
                )
            );
        }

        return true;
    }

    /**
     * Ensures state is ready for use
     * Prepares grid and any columns that require  preparation.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function prepare(): self
    {
        $this->init();

        // Verify if a name is set
        if (null === $this->getName()) {
            throw new Exception\InvalidArgumentException(
                'Grid has no name set.',
            );
        }

        // Verify if an adapter is set
        if (null === $this->getAdapter()) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    "Grid '%s' has no adapter set.",
                    $this->getName()
                )
            );
        }

        // If the user wants to, elements names can be wrapped by the form's name
        foreach ($this->getColumns() as $column) {
            if ($column instanceof ColumnPrepareAwareInterface) {
                $column->prepareColumn($this);
            }
        }

        $this->isPrepared = true;

        if (true === $this->isRequestForCurrentGrid()) {
            $this->setParams($this->getQueryParams());
        }

        return $this;
    }

    public function canFetchData(): bool
    {
        $this->checkIfIsPrepared();

        $queryName = $this->queryParams['_name'] ?? null;

        // Maji se nacist data pro jiny grid
        if ($queryName !== $this->getName()) {
            return false;
        }

        return true;
    }

    // ----- BUTTONS -----

    public function addButton(
        string $name,
        string $callback,
        ?string $label = null,
        ?string $icon = null
    ): self {
        $this->buttons[$name] = [
            'name' => $name,
            'label' => $label,
            'icon' => $icon,
            'callback' => $callback,
        ];

        return $this;
    }

    public function getButton(string $name): ?array
    {
        return $this->buttons[$name] ?? null;
    }

    public function getButtons(): ?array
    {
        return $this->buttons;
    }

    // ----- COLUMNS -----

    public function addColumn(ColumnInterface $column): self
    {
        $name = $column->getName();

        $this->columns[$name] = $column;

        return $this;
    }

    public function hasColumn(string $column): bool
    {
        return array_key_exists($column, $this->columns);
    }

    public function getColumn(string $column): ?ColumnInterface
    {
        if (!$this->hasColumn($column)) {
            return null;
        }

        return $this->columns[$column];
    }

    public function removeColumn(string $column): self
    {
        if (array_key_exists($column, $this->columns)) {
            unset($this->columns[$column]);
        }

        return $this;
    }

    /**
     * @param ColumnInterface[] $columns
     */
    public function setColumns(array $columns): self
    {
        $this->clearColumns();

        foreach ($columns as $column) {
            $this->addColumn($column);
        }

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function clearColumns(): self
    {
        $this->columns = [];

        return $this;
    }

    // ----- EVENT MANAGER -----

    #[\Override]
    public function setEventManager(EventManagerInterface $eventManager): self
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    #[\Override]
    public function getEventManager(): EventManagerInterface
    {
        if (null === $this->eventManager) {
            $this->eventManager = new EventManager();
        }

        return $this->eventManager;
    }

    // ----- PARAMS -----

    /**
     * Set params
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setParams(array $params): self
    {
        $valuesIterator = $this->getStorage()->read($this->getName());

        // Set params to storage
        $values = [];
        if ($this->canUseParams($params)) {
            foreach ($params as $key => $value) {
                $value = $this->modifyParam($key, $value);

                if (in_array($key, ['filters', 'page', 'rows', 'sidx', 'sord'])) {
                    $valuesIterator->offsetSet($key, $value);
                }
            }
        }

        $this->getStorage()->write($this->getName(), $valuesIterator);

        return $this;
    }

    /**
     * Get param
     */
    public function getParam(string $key): mixed
    {
        $storage = $this->getStorage()->read($this->getName());

        if ($this->hasParams() && $storage->offsetExists($key)) {
            return $storage->offsetGet($key);
        }

        return null;
    }

    /**
     * Get params from a specific namespace
     */
    public function getParams(): array
    {
        return $this->getStorage()->read($this->getName())->getArrayCopy();
    }

    /**
     * Exist param with given name?
     */
    public function hasParam(string $key): bool
    {
        if ($this->hasParams()) {
            return $this->getStorage()->read($this->getName())->offsetExists($key);
        }

        return false;
    }

    /**
     * Whether a specific namespace has params
     */
    public function hasParams(): bool
    {
        return $this->getStorage()->exists($this->getName());
    }

    private function modifyParam(string $key, mixed $value): mixed
    {
        // Modify params
        if ('filters' === $key) {
            if (is_array($value)) {
                $rules = $value;
            } else {
                $rules = json_decode((string) $value, true);
            }

            if (empty($rules['groupOp'])) {
                return $value;
            }

            $value = [];
            $value['operator'] = strtolower((string) $rules['groupOp']);
            foreach ($rules['rules'] as $rule) {
                $value['rules'][$rule['field']][] = [
                    'operator' => $this->getFilterOperator($rule['op']),
                    'value' => trim((string) $rule['data']),
                ];
            }
        }

        if ('rows' === $key) {
            $options = $this->getOptions();
            if (null !== $value && !in_array($value, $options->getRecordsPerPageList())) {
                $value = $options->getRecordsPerPage();
            }
        }

        // Don't save grid name to Session
        if ('_name' === $key) {
            $this->isRendered = true;
        }

        return $value;
    }

    private function canUseParams(array $params = []): bool
    {
        if (
            array_key_exists('_name', $params)
            && $params['_name'] === $this->getName()
        ) {
            return true;
        }

        return false;
    }

    // ----- COLUMN STYLES -----

    public function addColumnStyle(ColumnStyle $style): self
    {
        $this->columnStyles[] = $style;

        return $this;
    }

    /**
     * @param ColumnStyle[]|null $columnStyles
     */
    public function setColumnStyles(?array $columnStyles): self
    {
        if (null === $columnStyles) {
            $this->columnStyles = null;

            return $this;
        }

        foreach ($columnStyles as $columnStyle) {
            $this->addColumnStyle($columnStyle);
        }

        return $this;
    }

    /**
     * @return ColumnStyle[]|null
     */
    public function getColumnStyles(): ?array
    {
        return $this->columnStyles;
    }

    // ----- ROW STYLES -----

    public function addRowStyle(RowStyle $rowStyle): self
    {
        $this->rowStyles[] = $rowStyle;

        return $this;
    }

    /**
     * @param RowStyle[]|null $rowStyles
     */
    public function setRowStyles(?array $rowStyles): self
    {
        if (null === $rowStyles) {
            $this->rowStyles = null;

            return $this;
        }

        foreach ($rowStyles as $rowStyle) {
            $this->addRowStyle($rowStyle);
        }

        return $this;
    }

    /**
     * @return RowStyle[]|null
     */
    public function getRowStyles(): ?array
    {
        return $this->rowStyles;
    }

    // ----- RENDER -----

    /**
     * Return converted filter operator
     *
     * @throws Exception\InvalidArgumentException
     */
    public function getFilterOperator(string $operator): string
    {
        return match ($operator) {
            'eq' => OperatorConstant::OPERATOR_EQUAL,
            'ne' => OperatorConstant::OPERATOR_NOT_EQUAL,
            'lt' => OperatorConstant::OPERATOR_LESS,
            'le' => OperatorConstant::OPERATOR_LESS_OR_EQUAL,
            'gt' => OperatorConstant::OPERATOR_GREATER,
            'ge' => OperatorConstant::OPERATOR_GREATER_OR_EQUAL,
            'bw' => OperatorConstant::OPERATOR_BEGINS_WITH,
            'bn' => OperatorConstant::OPERATOR_NOT_BEGINS_WITH,
            'in' => OperatorConstant::OPERATOR_IN,
            'ni' => OperatorConstant::OPERATOR_NOT_IN,
            'ew' => OperatorConstant::OPERATOR_ENDS_WITH,
            'en' => OperatorConstant::OPERATOR_NOT_ENDS_WITH,
            'cn' => OperatorConstant::OPERATOR_CONTAINS,
            'nc' => OperatorConstant::OPERATOR_NOT_CONTAINS,
            default => throw new Exception\InvalidArgumentException('Invalid filter operator'),
        };
    }

    /**
     * Return converted filter operator
     *
     * @throws Exception\InvalidArgumentException
     */
    public function getFilterOperatorOutput(string $operator): string
    {
        return match ($operator) {
            OperatorConstant::OPERATOR_EQUAL => 'eq',
            OperatorConstant::OPERATOR_NOT_EQUAL => 'ne',
            OperatorConstant::OPERATOR_LESS => 'lt',
            OperatorConstant::OPERATOR_LESS_OR_EQUAL => 'le',
            OperatorConstant::OPERATOR_GREATER => 'gt',
            OperatorConstant::OPERATOR_GREATER_OR_EQUAL => 'ge',
            OperatorConstant::OPERATOR_BEGINS_WITH => 'bw',
            OperatorConstant::OPERATOR_NOT_BEGINS_WITH => 'bn',
            OperatorConstant::OPERATOR_IN => 'in',
            OperatorConstant::OPERATOR_NOT_IN => 'ni',
            OperatorConstant::OPERATOR_ENDS_WITH => 'ew',
            OperatorConstant::OPERATOR_NOT_ENDS_WITH => 'en',
            OperatorConstant::OPERATOR_CONTAINS => 'cn',
            OperatorConstant::OPERATOR_NOT_CONTAINS => 'nc',
            default => throw new Exception\InvalidArgumentException('Invalid filter operator'),
        };
    }

    /**
     * Get number of current page
     */
    public function getNumberOfCurrentPage(): int
    {
        $page = $this->getOptions()->getPage();

        if ($this->hasParam('page')) {
            $param = $this->getParam('page');
            if (!empty($param)) {
                $page = (int) $param;
            }
        }

        return $page;
    }

    /**
     * Get number of visible rows
     */
    public function getNumberOfVisibleRows(): int
    {
        $number = $this->getOptions()->getRecordsPerPage();

        if ($this->hasParam('rows')) {
            $param = $this->getParam('rows');

            if (
                !empty($param)
                && in_array($param, $this->getOptions()->getRecordsPerPageList())
            ) {
                $number = (int) $param;
            }
        }

        return $number;
    }

    /**
     * Return sort by column name => direct
     */
    public function getSort(): array
    {
        $sort = [];

        // Nacteme vychozi razeni
        $column = $this->getOptions()->getSortName();
        $direct = $this->getOptions()->getSortOrder();

        // Nacteme razeni z parametru z Requestu
        if ($this->hasParam('sidx')) {
            $sidx = $this->getParam('sidx');
            if (!empty($sidx)) {
                $column = $sidx;
            }
        }
        if ($this->hasParam('sord')) {
            $sord = $this->getParam('sord');
            if (!empty($sord)) {
                $direct = $sord;
            }
        }

        // Osetrime vstup
        $column = trim((string) $column);
        $direct = trim((string) $direct);

        if (
            false === $this->hasColumn($column)
            || !in_array(strtolower($direct), ['asc', 'desc'])
        ) {
            return $sort;
        }

        // Sestavime shodne retezce ve formatu (sloupec smer)
        if (strpos($column, ', ')) {
            $parts = explode(', ', $column);
            $partsCount = count($parts);

            // Doplnime
            $parts[$partsCount - 1] .= ' ' . $direct;
        } elseif (!empty($column)) {
            $parts[] = $column . ' ' . $direct;
        }

        // Z jednotlivych casti sestavime pole ve formatu (sloupec => smer)
        if (!empty($parts)) {
            foreach ($parts as $part) {
                $subParts = explode(' ', $part);

                $sort[$subParts[0]] = $subParts[1];
            }
        }

        return $sort;
    }
}
