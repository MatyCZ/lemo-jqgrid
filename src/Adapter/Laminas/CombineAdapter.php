<?php

namespace Lemo\JqGrid\Adapter\Laminas;

use DateTime;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Combine;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Predicate;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Lemo\JqGrid\Adapter\AbstractAdapter;
use Lemo\JqGrid\Column\Concat as ColumnConcat;
use Lemo\JqGrid\ColumnAttributes;
use Lemo\JqGrid\ColumnInterface;
use Lemo\JqGrid\Constant\OperatorConstant;
use Lemo\JqGrid\Event\AdapterEvent;
use Lemo\JqGrid\Exception;
use Lemo\JqGrid\Grid;

use function array_values;
use function count;
use function explode;
use function in_array;
use function is_array;
use function preg_match_all;
use function reset;
use function str_replace;

class CombineAdapter extends AbstractAdapter
{
    protected ?AdapterInterface $adapter = null;
    protected ?Combine $combine = null;
    protected ?Select $select = null;

    #[\Override]
    public function prepare(Grid $grid): self
    {
        if ($this->isPrepared) {
            return $this;
        }

        $this->setGrid($grid);

        if (!$this->getCombine() instanceof Combine) {
            throw new Exception\UnexpectedValueException(sprintf("No '%s' instance given", Combine::class));
        }

        $this->applyFilters();
        $this->applyPagination();
        $this->applySortings();

        $this->isPrepared = true;

        return $this;
    }

    /**
     * @throws Exception\UnexpectedValueException
     */
    #[\Override]
    public function fetchData(): self
    {
        $columns = $this->getGrid()->getColumns();

        $rows = $this->executeFetchRows();
        $rowsCount = count($rows);
        $rowsTotal = $this->executeFetchRowsTotal();

        // Update count of items
        $this->countItems = $rowsCount;
        $this->countItemsTotal = $rowsTotal;

        $data = [];
        for ($indexRow = 0; $indexRow < $rowsCount; $indexRow++) {
            $item = (array) $rows[$indexRow];

            foreach ($columns as $column) {
                $colName = $column->getName();
                $data[$indexRow][$colName] = null;

                // Can we render value?
                if (true === $column->isValid($this, $item)) {

                    // Nacteme si data radku
                    $value = $this->findValue($colName, $item);

                    // COLUMN - DateTime
                    if ($value instanceof DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $column->setValue($value);

                    $value = $column->renderValue($this, $item);

                    // Projdeme data a nahradime data ve formatu %xxx%
                    if (null !== $value && preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', (string) $value, $matches)) {
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

        $this->getGrid()->getResultSet()->setData($data);

        $event = new AdapterEvent();
        $event->setGrid($this->getGrid());

        $this->getGrid()->getEventManager()->trigger(
            AdapterEvent::EVENT_FETCH_DATA,
            $this,
            $event
        );

        return $this;
    }

    public function executeFetchRows(): array
    {
        $zendSql = new Sql($this->getAdapter());

        $sqlCombine = $zendSql->buildSqlString($this->getCombine());
        $sql = $zendSql->buildSqlString($this->getSelect());
        $sql = str_replace(['`tmp`.*', '`%s`'], ['*', '(' . $sqlCombine . ')'], $sql);

        $result = $this->getAdapter()->getDriver()->getConnection()->execute($sql);

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet->toArray();
    }

    /**
     * @return int
     */
    public function executeFetchRowsTotal(): int
    {
        $zendSql = new Sql($this->getAdapter());

        $sql = $zendSql->buildSqlString($this->getSelect()->reset('limit')->reset('offset')->reset('order'));
        $sqlCombine = $zendSql->buildSqlString($this->getCombine());
        $sql = str_replace(['`tmp`.*', '`%s`'], ['COUNT(*) as count', '(' . $sqlCombine . ')'], $sql);

        $result = $this->getAdapter()->getDriver()->getConnection()->execute($sql);

        if ($result->count() > 0) {
            foreach ($result as $row) {
                $row = array_values($row);
                return (int) $row[0];
            }
        }

        return 0;
    }

    /**
     * Apply filters to the Select
     *
     * @return $this
     */
    protected function applyFilters(): self
    {
        $columns = $this->getGrid()->getColumns();
        $filter = $this->getGrid()->getParam('filters');

        // WHERE
        if (!empty($filter['rules'])) {
            $havingCol = [];
            $whereCol = [];
            foreach($columns as $col) {
                if (true === $col->getAttributes()->getIsSearchable() && true !== $col->getAttributes()->getIsHidden()) {

                    // Jsou definovane filtry pro sloupec
                    if (!empty($filter['rules'][$col->getName()])) {

                        $whereColSub = [];
                        foreach ($filter['rules'][$col->getName()] as $filterDefinition) {
                            if (in_array((string) $filterDefinition['operator'], ['~', '!~'])) {

                                // Odstranime duplicity a prazdne hodnoty
                                $filterWords = [];
                                foreach (explode(' ', (string) $filterDefinition['value']) as $word) {
                                    if (in_array($word, $filterWords)) {
                                        continue;
                                    }

                                    if ('' === $word) {
                                        continue;
                                    }

                                    $filterWords[] = $word;
                                }

                                if (empty($filterWords)) {
                                    continue;
                                }

                                if ($col instanceof ColumnConcat) {

                                    $concat = $this->buildConcat($col->getOptions()->getIdentifiers());

                                    $predicateColSub = new Predicate();
                                    foreach ($filterWords as $filterWord) {
                                        $predicate = $this->buildWhereFromFilter(
                                            $col,
                                            $concat,
                                            [
                                                'operator' => $filterDefinition['operator'],
                                                'value' => $filterWord
                                            ]
                                        );

                                        // Urcime pomoci jakeho operatoru mame skladat jednotlive vyrazi hledani sloupce
                                        if (ColumnAttributes::SEARCH_GROUPOPERATOR_AND === $col->getAttributes()->getSearchGroupOperator()) {
                                            $predicateColSub->andPredicate($predicate);
                                        } elseif ('~' === $filterDefinition['operator']) {
                                            $predicateColSub->orPredicate($predicate);
                                        } else {
                                            $predicateColSub->andPredicate($predicate);
                                        }
                                    }

                                    $whereColSub[] = $predicateColSub;
                                } else {

                                    $predicateColSub = new Predicate();
                                    foreach ($filterWords as $filterWord) {
                                        $predicate = $this->buildWhereFromFilter($col, $col->getIdentifier(), [
                                            'operator' => $filterDefinition['operator'],
                                            'value' => $filterWord,
                                        ]);

                                        if (ColumnAttributes::SEARCH_GROUPOPERATOR_AND === $col->getAttributes()->getSearchGroupOperator()) {
                                            $predicateColSub->andPredicate($predicate);
                                        } elseif ('~' === $filterDefinition['operator']) {
                                            $predicateColSub->orPredicate($predicate);
                                        } else {
                                            $predicateColSub->andPredicate($predicate);
                                        }
                                    }

                                    $whereColSub[] = $predicateColSub;
                                }
                            } else {

                                // Sestavime filtr pro jednu podminku sloupce
                                $predicateColSub = new Predicate();
                                if ($col instanceof ColumnConcat) {
                                    foreach ($col->getOptions()->getIdentifiers() as $identifier) {
                                        $predicateColSub->orPredicate($this->buildWhereFromFilter($col, $identifier, $filterDefinition));
                                    }
                                } else {
                                    $predicateColSub->orPredicate($this->buildWhereFromFilter($col, $col->getIdentifier(), $filterDefinition));
                                }

                                // Sloucime podminky sloupce pomoci OR (z duvodu Concat sloupce)
                                $whereColSub[] = $predicateColSub;
                            }
                        }

                        // Urcime pomoci jako operatoru mame sloupcit jednotlive podminky
                        if (!empty($whereColSub)) {
                            if (count($whereColSub) == 1) {
                                $predicateCol = $whereColSub[0];
                            } else {
                                $predicateCol = new Predicate();
                                foreach ($whereColSub as $w) {
                                    if ('and' === $filter['operator']) {
                                        $predicateCol->andPredicate($w);
                                    } else {
                                        $predicateCol->orPredicate($w);
                                    }
                                }
                            }

                            switch ($col->getAttributes()->getSearchType()) {
                                case ColumnAttributes::SEARCH_TYPE_HAVING:
                                    $havingCol[] = $predicateCol;
                                    break;
                                case ColumnAttributes::SEARCH_TYPE_WHERE:
                                    $whereCol[] = $predicateCol;
                                    break;
                            }
                        }
                    }
                }
            }

            // Pridame k vychozimu HAVING i HAVING z filtrace sloupcu
            if (!empty($havingCol)) {
                if (count($havingCol) == 1) {
                    $predicate = $havingCol[0];
                } else {
                    $predicate = new Predicate();
                    foreach ($havingCol as $w) {
                        if ('and' === $filter['operator']) {
                            $predicate->andPredicate($w);
                        } else {
                            $predicate->orPredicate($w);
                        }
                    }
                }

                $this->getSelect()->having($predicate);
            }

            // Pridame k vychozimu WHERE i WHERE z filtrace sloupcu
            if (!empty($whereCol)) {
                if (count($whereCol) == 1) {
                    $predicate = $whereCol[0];
                } else {
                    $predicate = new Predicate();
                    foreach ($whereCol as $w) {
                        if ('and' == $filter['operator']) {
                            $predicate->andPredicate($w);
                        } else {
                            $predicate->orPredicate($w);
                        }
                    }
                }

                $this->getSelect()->where($predicate);
            }
        }

        return $this;
    }

    /**
     * Apply pagination to the Select
     */
    protected function applyPagination(): self
    {
        $numberCurrentPage = $this->getGrid()->getNumberOfCurrentPage();
        $numberVisibleRows = $this->getGrid()->getNumberOfVisibleRows();

        // Calculate offset
        if ($numberVisibleRows > 0) {
            $offset = $numberVisibleRows * $numberCurrentPage - $numberVisibleRows;
            if($offset < 0) {
                $offset = 0;
            }

            $this->getSelect()->limit($numberVisibleRows);
            $this->getSelect()->offset((int) $offset);
        }

        return $this;
    }

    /**
     * Apply sorting to the QueryBuilder
     */
    protected function applySortings(): self
    {
        $sort = $this->getGrid()->getSort();

        // Store default order to variable and reset orderBy
        $orderBy = $this->getSelect()->getRawState('order');
        $this->getSelect()->reset('order');

        // ORDER
        if (!empty($sort)) {
            foreach ($sort as $sortColumn => $sortDirect) {
                if ($this->getGrid()->hasColumn($sortColumn)) {
                    if (false !== $this->getGrid()->getColumn($sortColumn)->getAttributes()->getIsSortable() && true !== $this->getGrid()->getColumn($sortColumn)->getAttributes()->getIsHidden()) {
                        if ($this->getGrid()->getColumn($sortColumn) instanceof ColumnConcat) {
                            foreach ($this->getGrid()->getColumn($sortColumn)->getOptions()->getIdentifiers() as $identifier){
                                $this->getSelect()->order($identifier . ' ASC');
                            }
                        } else {
                            $this->getSelect()->order($this->getGrid()->getColumn($sortColumn)->getIdentifier() . ' ' . $sortDirect);
                        }
                    }
                }
            }
        }

        // Add default order from variable
        if (!empty($orderBy)) {
            foreach ($orderBy as $order) {
                $this->getSelect()->order($order);
            }
        }

        return $this;
    }

    /**
     * Find value for column
     */
    #[\Override]
    public function findValue(string $identifier, array $item, int $depth = 0): mixed
    {
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
     * Sestavi CONCAT z predanych casti
     *
     * @return Expression
     */
    protected function buildConcat(array $identifiers)
    {
        if (count($identifiers) > 1) {
            $parts = [];
            foreach ($identifiers as $identifier) {
                $parts[] = "CASE WHEN  (" . $identifier . " IS NULL) THEN '' ELSE " . $identifier . " END";
            }

            return new Expression('CONCAT', $parts);
        }

        return reset($identifiers);
    }


    /**
     * @throws Exception\InvalidArgumentException
     */
    protected function buildWhereFromFilter(
        ColumnInterface $column,
        string $identifier,
        array $filterDefinition
    ): Predicate {
        $predicate = new Predicate();

        $value = $filterDefinition['value'];
        $operator = $filterDefinition['operator'];

        // Pravedeme neuplny string na DbDate
        if (ColumnAttributes::FORMAT_DATE === $column->getAttributes()->getFormat()) {
            $value = $this->convertLocaleDateToDbDate($value);
        }

        switch ($operator) {
            case OperatorConstant::OPERATOR_EQUAL:
                return $predicate->equalTo($identifier, $value);
            case OperatorConstant::OPERATOR_NOT_EQUAL:
                return $predicate->notEqualTo($identifier, $value);
            case OperatorConstant::OPERATOR_LESS:
                return $predicate->lessThan($identifier, $value);
            case OperatorConstant::OPERATOR_LESS_OR_EQUAL:
                return $predicate->lessThanOrEqualTo($identifier, $value);
            case OperatorConstant::OPERATOR_GREATER:
                return $predicate->greaterThan($identifier, $value);
            case OperatorConstant::OPERATOR_GREATER_OR_EQUAL:
                return $predicate->greaterThanOrEqualTo($identifier, $value);
            case OperatorConstant::OPERATOR_BEGINS_WITH:
                return $predicate->like($identifier, $value . "%");
            case OperatorConstant::OPERATOR_NOT_BEGINS_WITH:
                return $predicate->notLike($identifier, $value . "%");
            case OperatorConstant::OPERATOR_IN:
                if (!is_array($value)) {
                    $value = explode(',', (string) $value);
                }
                if (!empty($value)) {
                    $predicate->in($identifier, $value);
                }

                return $predicate;
            case OperatorConstant::OPERATOR_NOT_IN:
                if (!is_array($value)) {
                    $value = explode(',', (string) $value);
                }
                if (!empty($value)) {
                    $predicate->notIn($identifier, $value);
                }

                return $predicate;
            case OperatorConstant::OPERATOR_ENDS_WITH:
                return $predicate->like($identifier, "%" . $value);
            case OperatorConstant::OPERATOR_NOT_ENDS_WITH:
                return $predicate->notLike($identifier, "%" . $value);
            case OperatorConstant::OPERATOR_CONTAINS:
                return $predicate->like($identifier, "%" . $value . "%");
            case OperatorConstant::OPERATOR_NOT_CONTAINS:
                return $predicate->notLike($identifier, "%" . $value . "%");
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }
    }

    public function setAdapter(?AdapterInterface $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter(): ?AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Set Combine
     */
    public function setCombine(?Combine $combine): self
    {
        $this->combine = $combine;

        return $this;
    }

    /**
     * Return Combine
     */
    public function getCombine(): ?Combine
    {
        return $this->combine;
    }

    /**
     * @return Select
     */
    protected function getSelect(): Select
    {
        if (null === $this->select) {
            $select = new Select();
            $select->from(['tmp' => '%s']);

            $this->select = $select;
        }

        return $this->select;
    }
}
