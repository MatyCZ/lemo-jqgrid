<?php

namespace Lemo\JqGrid\Adapter\Php;

use DateTime;
use Generator;
use Lemo\JqGrid\Adapter\AbstractAdapter;
use Lemo\JqGrid\Column\Concat as ColumnConcat;
use Lemo\JqGrid\ColumnAttributes;
use Lemo\JqGrid\ColumnInterface;
use Lemo\JqGrid\Constant\OperatorConstant;
use Lemo\JqGrid\Event\AdapterEvent;
use Lemo\JqGrid\Exception;
use Lemo\JqGrid\Grid;
use Throwable;

use function array_sum;
use function count;
use function explode;
use function in_array;
use function max;
use function min;
use function preg_match;
use function preg_match_all;
use function str_contains;
use function str_replace;
use function strpos;
use function substr;

class ArrayAdapter extends AbstractAdapter
{
    protected array $dataFiltered = [];

    /**
     * Constuctor
     *
     * @param array $dataSource Data as key => value or only values
     * @param array $relations Relation as relation alias => array field
     */
    public function __construct(
        protected array $dataSource = [],
        protected array $relations = []
    ) {}

    #[\Override]
    public function prepare(Grid $grid): self
    {
        if ($this->isPrepared) {
            return $this;
        }

        $this->setGrid($grid);

        $this->isPrepared = true;

        return $this;
    }

    /**
     * @return Generator
     */
    public function getExportGenerator(array $selectedRows = []): Generator
    {
        try {
            $rows = $this->getDataSource();
            $columns = $this->getGrid()->getColumns();

            $rowIdColumn = $this->getGrid()->getOptions()->getRowIdColumn();

            if (empty($rows)) {
                yield 0;
            } else {
                yield count($rows);
            }

            foreach ($rows as $item) {

                $result = [];
                if (null !== $rowIdColumn && !empty($item[$rowIdColumn])) {
                    if (
                        !empty($selectedRows)
                        && !in_array($item[$rowIdColumn], $selectedRows)
                    ) {
                        continue;
                    }

                    $result['rowId'] = $item[$rowIdColumn];
                }

                foreach ($columns as $column) {
                    $colIdentifier = $column->getIdentifier();
                    $colName = $column->getName();
                    $result[$colName] = null;

                    if (true === $column->isValid($this, $item)) {

                        $value = $this->findValue($colIdentifier, $item);

                        // COLUMN - DateTime
                        if ($value instanceof DateTime) {
                            $value = $value->format('Y-m-d H:i:s');
                        }

                        $column->setValue($value);
                        $value = $column->renderValue($this, $item);

                        $result[$colName] = $value;
                    }
                }

                yield $result;
            }

        } catch (Throwable $throwable) {
            yield $throwable;
        }
    }

    /**
     * Load data
     */
    #[\Override]
    public function fetchData(): self
    {
        $rows = $this->getDataSource();
        $rowsCount = count($rows);
        $columns = $this->getGrid()->getColumns();

        $rowIdColumn = $this->getGrid()->getOptions()->getRowIdColumn();

        // Nacteme si kolekci dat
        $data = [];
        for ($indexRow = 0; $indexRow < $rowsCount; $indexRow++) {
            $item = $rows[$indexRow];

            if (null !== $rowIdColumn && !empty($item[$rowIdColumn])) {
                $data[$indexRow]['rowId'] = $item[$rowIdColumn];
            }

            foreach($columns as $indexCol => $column) {
                $colIdentifier = $column->getIdentifier();
                $colName = $column->getName();
                $data[$indexRow][$colName] = null;

                // Can we render value?
                if (true === $column->isValid($this, $item)) {

                    // Nacteme si data radku
                    $value = $this->findValue($colIdentifier, $item);

                    // COLUMN - DateTime
                    if ($value instanceof DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $column->setValue($value);

                    $value = $column->renderValue($this, $item);

                    // Projdeme data a nahradime data ve formatu %xxx%
                    if (null !== preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', (string) $value, $matches)) {
                        foreach ($matches[0] as $key => $match) {
                            if ('%_index%' === $matches[0][$key]) {
                                $value = str_replace(
                                    $matches[0][$key],
                                    (string) $indexRow,
                                    (string) $value
                                );
                            } else {
                                $value = str_replace(
                                    $matches[0][$key],
                                    $this->findValue($matches[1][$key], $item),
                                    (string) $value
                                );
                            }
                        }
                    }

                    $data[$indexRow][$colName] = $value;
                    $column->setValue($value);
                }
            }
        }

        // Modify collection
        $data = $this->applyFilters($data);
        $data = $this->applySortings($data);

        // Set total count of items
        $this->countItemsTotal = count($data);
        $this->dataFiltered = $data;

        // Paginate collection
        $data = $this->applyPagination($data);

        // Set count of items
        $this->countItems = count($data);

        $this->getGrid()->getResultSet()->setData($data);

        // Fetch summary data
        $this->fetchDataSummary();

        $event = new AdapterEvent();
        $event->setGrid($this->getGrid());

        $this->getGrid()->getEventManager()->trigger(
            AdapterEvent::EVENT_FETCH_DATA,
            $this,
            $event
        );

        return $this;
    }

    protected function fetchDataSummary(): self
    {
        if (true === $this->getGrid()->getOptions()->getUserDataOnFooter()) {
            $items = $this->dataFiltered;
            $itemsCount = count($items);

            // Find columns data for summary
            $columnsValues = [];
            for ($indexItem = 0; $indexItem < $itemsCount; $indexItem++) {
                $item = $items[$indexItem];

                foreach ($this->getGrid()->getColumns() as $column) {
                    $colName = $column->getName();

                    // Can we render value?
                    if (
                        null !== $column->getAttributes()->getSummaryType()
                        && true === $column->isValid($this, $item)
                    ) {
                        $columnsValues[$colName][$indexItem] = $item[$colName];
                    }
                }
            }

            // Calculate user data (SummaryRow)
            $dataUser = [];
            foreach ($this->getGrid()->getColumns() as $column) {

                // Sloupec je skryty, takze ho preskocime
                if (true === $column->getAttributes()->getIsHidden()) {
                    continue;
                }

                // Sloupec je skryty, musime ho preskocit
                if (true === (bool) $column->getAttributes()->getIsHidden()) {
                    continue;
                }

                if (null !== $column->getAttributes()->getSummaryType()) {
                    $colName = $column->getName();
                    $dataUser[$colName] = '';
                    $summaryType = $column->getAttributes()->getSummaryType();

                    if (isset($columnsValues[$colName])) {
                        if ('sum' == $summaryType) {
                            $dataUser[$colName] = array_sum($columnsValues[$colName]);
                        }
                        if ('min' == $summaryType) {
                            $dataUser[$colName] = min($columnsValues[$colName]);
                        }
                        if ('max' == $summaryType) {
                            $dataUser[$colName] = max($columnsValues[$colName]);
                        }
                        if ('count' == $summaryType) {
                            $dataUser[$colName] = array_sum($columnsValues[$colName]) / count($columnsValues[$colName]);
                        }
                    }
                }
            }

            $this->getGrid()->getResultSet()->setDataUser($dataUser);
        }

        return $this;
    }

    /**
     * Apply filters to the collection
     */
    protected function applyFilters(array $rows): array
    {
        $grid = $this->getGrid();
        $filter = $grid->getParam('filters');

        if (empty($rows) || empty($filter['rules'])) {
            return $rows;
        }

        $columns = $this->getGrid()->getColumns();

        foreach ($rows as $indexRow => $item) {

            if (!empty($columns)) {
                foreach ($columns as $column) {

                    // Ma sloupec povolene vyhledavani?
                    if (
                        true === $column->getAttributes()->getIsSearchable()
                        && true !== $column->getAttributes()->getIsHidden()
                    ) {

                        // Jsou definovane filtry pro sloupec
                        if (!empty($filter['rules'][$column->getName()])) {
                            foreach ($filter['rules'][$column->getName()] as $filterDefinition) {
                                if ($column instanceof ColumnConcat) {
                                    preg_match(
                                        '/' . $filterDefinition['value'] . '/i',
                                        (string) $item[$column->getName()],
                                        $matches
                                    );

                                    if (count($matches) == 0) {
                                        unset($rows[$indexRow]);
                                    }
                                } elseif (false === $this->buildWhereFromFilter($column, $filterDefinition, $item[$column->getName()])) {
                                    unset($rows[$indexRow]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * Apply pagination to the collection
     */
    protected function applyPagination(array $rows): array
    {
        $numberCurrentPage = $this->getGrid()->getNumberOfCurrentPage();
        $numberVisibleRows = $this->getGrid()->getNumberOfVisibleRows();

        // Strankovani
        if ($numberVisibleRows > 0) {
            $rows = array_slice(
                $rows,
                $numberVisibleRows * $numberCurrentPage - $numberVisibleRows,
                $numberVisibleRows
            );
        }

        return $rows;
    }

    /**
     * Apply sorting to the collection
     */
    protected function applySortings(array $rows): array
    {
        $grid = $this->getGrid();
        $sort = $this->getGrid()->getSort();

        if (empty($rows) || empty($sort)) {
            return $rows;
        }

        $arguments = [];
        foreach ($sort as $sortColumn => $sortDirect) {
            if ($grid->hasColumn($sortColumn)) {
                if (false !== $grid->getColumn($sortColumn)->getAttributes()->getIsSortable() && true !== $grid->getColumn($sortColumn)->getAttributes()->getIsHidden()) {

                    $columnValues = [];
                    foreach ($rows as $rowValues) {
                        $columnValues[] = $rowValues[$sortColumn] ?? null;
                    }

                    $arguments[] = $columnValues;
                    $arguments[] = ('asc' === $sortDirect) ? SORT_ASC : SORT_DESC;
                }
            }
        }

        $arguments[] = &$rows;

        call_user_func_array('array_multisort', $arguments);

        return $rows;
    }

    /**
     * Find value for column
     */
    #[\Override]
    public function findValue(string $identifier, array $item, int $depth = 0): mixed
    {
        // Determinate column name and alias name
        $identifier = str_replace('_', '.', $identifier);

        if (str_contains($identifier, '.')) {
            $identifier = substr($identifier, strpos($identifier, '.') + 1);
        }

        $parts = explode('.', $identifier);
        if (isset($item[$parts[0]]) && count($parts) > 1) {
            return $this->findValue($identifier, $item[$parts[0]], $depth + 1);
        }

        if (isset($item[$identifier])) {
            return $item[$identifier];
        } elseif (isset($item[0])) {
            $return = [];
            foreach ($item as $it) {
                if (isset($it[$identifier])) {
                    $return[] = $it[$identifier];
                }
            }

            return $return;
        }

        return null;
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    protected function buildWhereFromFilter(ColumnInterface $column, array $filterDefinition, string $value): bool
    {
        $isValid = true;
        $operator = $filterDefinition['operator'];
        $valueFilter = $filterDefinition['value'];

        // Pravedeme neuplny string na DbDate
        if (ColumnAttributes::FORMAT_DATE === $column->getAttributes()->getFormat()) {
            $valueFilter = $this->convertLocaleDateToDbDate($valueFilter);
        }

        switch ($operator) {
            case OperatorConstant::OPERATOR_EQUAL:
                if ($value != $valueFilter) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_NOT_EQUAL:
                if ($value == $valueFilter) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_LESS:
                if ($value >= $valueFilter) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_LESS_OR_EQUAL:
                if ($value > $valueFilter) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_GREATER:
                if ($value <= $valueFilter) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_GREATER_OR_EQUAL:
                if ($value < $valueFilter) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_BEGINS_WITH:
                $count = preg_match('/^' . $valueFilter . '/i', $value, $matches);
                if (0 === $count) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_NOT_BEGINS_WITH:
                $count = preg_match('/^' . $valueFilter . '/i', $value, $matches);
                if ($count > 0) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_IN:
                break;
            case OperatorConstant::OPERATOR_NOT_IN:
                break;
            case OperatorConstant::OPERATOR_ENDS_WITH:
                $count = preg_match('/' . $valueFilter . '$/i', $value, $matches);
                if (0 === $count) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_NOT_ENDS_WITH:
                $count = preg_match('/' . $valueFilter . '$/i', $value, $matches);
                if ($count > 0) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_CONTAINS:
                $count = preg_match('/' . $valueFilter . '/i', $value, $matches);
                if (0 === $count) {
                    $isValid = false;
                }
                break;
            case OperatorConstant::OPERATOR_NOT_CONTAINS:
                $count = preg_match('/' . $valueFilter . '/i', $value, $matches);
                if ($count > 0) {
                    $isValid = false;
                }
                break;
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }

        return $isValid;
    }

    public function setDataSource(array $dataSource): self
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    public function getDataSource(): array
    {
        return $this->dataSource;
    }

    public function setRelations(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }
}
