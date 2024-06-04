<?php

namespace Lemo\JqGrid\View\Helper;

use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Helper\AbstractHelper;
use Lemo\JqGrid\ColumnAttributes;
use Lemo\JqGrid\ColumnInterface;
use Lemo\JqGrid\ColumnStyle;
use Lemo\JqGrid\Event\RendererEvent;
use Lemo\JqGrid\Exception;
use Lemo\JqGrid\Grid;
use Lemo\JqGrid\GridOptions;
use Lemo\JqGrid\RowStyle;

use function array_key_exists;
use function array_values;
use function count;
use function current;
use function http_build_query;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function parse_str;
use function parse_url;
use function strtolower;
use function strtoupper;

use const JSON_THROW_ON_ERROR;
use const PHP_EOL;

class JqGrid extends AbstractHelper
{
    /**
     * List of valid column attributes with jqGrid attribute name
     */
    protected array $columnAttributes = [
        'align'                => 'align',
        'column_attributes'    => 'cellattr',
        'class'                => 'classes',
        'date_format'          => 'datefmt',
        'default_value'        => 'defval',
        'edit_element'         => 'edittype',
        'edit_element_options' => 'formoptions',
        'edit_options'         => 'editoptions',
        'edit_rules'           => 'editrules',
        'format'               => 'formatter',
        'format_options'       => 'formatoptions',
        'name'                 => 'name',
        'is_editable'          => 'isEditable',
        'is_fixed'             => 'fixed',
        'is_frozen'            => 'frozen',
        'is_hidden'            => 'hidden',
        'is_hideable'          => 'hidedlg',
        'is_searchable'        => 'search',
        'is_sortable'          => 'sortable',
        'is_resizable'         => 'resizable',
        'label'                => 'label',
        'search_element'       => 'stype',
        'search_options'       => 'searchOptions',
        'search_url'           => 'surl',
        'sort_type'            => 'sortType',
        'show_title'           => 'title',
        'summary_tpl'          => 'summaryTpl',
        'summary_type'         => 'summaryType',
        'width'                => 'width',
    ];

    /**
     * List of valid grid attributes with jqGrid attribute name
     */
    protected array $gridAttributes = [
        'alternative_rows'                   => 'altRows',
        'alternative_rows_class'             => 'altclass',
        'auto_encode_incoming_and_post_data' => 'autoencode',
        'autowidth'                          => 'autowidth',
        'caption'                            => 'caption',
        'cell_edit'                          => 'cellEdit',
        'cell_edit_url'                      => 'editurl',
        'cell_layout'                        => 'cellLayout',
        'cell_save_type'                     => 'cellsubmit',
        'cell_save_url'                      => 'cellurl',
        'data_string'                        => 'datastr',
        'data_type'                          => 'datatype',
        'default_page'                       => 'page',
        'expand_column_identifier'           => 'ExpandColumn',
        'expand_column_on_click'             => 'ExpandColClick',
        'force_fit'                          => 'forceFit',
        'grid_state'                         => 'gridstate',
        'grid_view'                          => 'gridview',
        'grouping'                           => 'grouping',
        'header_titles'                      => 'headertitles',
        'height'                             => 'height',
        'hover_rows'                         => 'hoverrows',
        'icon_set'                           => 'iconSet',
        'load_once'                          => 'loadonce',
        'load_type'                          => 'loadui',
        'multi_select'                       => 'multiselect',
        'multi_select_key'                   => 'multikey',
        'multi_select_width'                 => 'multiselectWidth',
        'multi_sort'                         => 'multiSort',
        'page'                               => 'page',
        'pager_element_id'                   => 'pager',
        'pager_position'                     => 'pagerpos',
        'pager_show_buttions'                => 'pgbuttons',
        'pager_show_input'                   => 'pginput',
        'record_position'                    => 'recordpos',
        'records_per_page'                   => 'rowNum',
        'records_per_page_list'              => 'rowList',
        'render_hide_grid_button'            => 'hidegrid',
        'render_footer_row'                  => 'footerrow',
        'render_records_info'                => 'viewrecords',
        'render_row_numbers_column'          => 'rownumbers',
        'request_type'                       => 'mtype',
        'resize_class'                       => 'resizeclass',
        'scroll'                             => 'scroll',
        'scroll_offset'                      => 'scrollOffset',
        'scroll_rows'                        => 'scrollRows',
        'scroll_timeout'                     => 'scrollTimeout',
        'shrink_to_fit'                      => 'shrinkToFit',
        'sort_name'                          => 'sortname',
        'sort_order'                         => 'sortorder',
        'sorting_columns'                    => 'sortable',
        'sorting_columns_definition'         => 'viewsortcols',
        'tree_grid'                          => 'treeGrid',
        'tree_grid_icons'                    => 'treeIcons',
        'tree_grid_type'                     => 'treeGridModel',
        'url'                                => 'url',
        'user_data'                          => 'userData',
        'user_data_on_footer'                => 'userDataOnFooter',
        'width'                              => 'width',
    ];

    public function __construct(
        protected readonly ?TranslatorInterface $translator = null
    ) {}

    /**
     * Render grid HTML and scripts
     *
     * @throws Exception\UnexpectedValueException
     */
    public function __invoke(Grid $grid): string
    {
        $grid->checkIfIsPrepared();

        $this->gridModifyAttributes($grid);

        $event = new RendererEvent();
        $event->setGrid($grid);

        $grid->getEventManager()->trigger(
            RendererEvent::EVENT_RENDER,
            $this,
            $event
        );

        $this->getView()->inlineScript()->appendScript(
            $this->renderScript($grid)
        );
        $this->getView()->inlineScript()->appendScript(
            $this->renderScriptAutoresize($grid)
        );

        return $this->renderHtml($grid);

//        return $this->renderHtml($grid) . PHP_EOL
//            . $this->renderScript($grid) . PHP_EOL
//            . $this->renderScriptAutoresize($grid);
    }

    /**
     * Render HTML of the grid
     */
    public function renderHtml(Grid $grid): string
    {
        $html = [];
        $html[] = '<table id="' . $grid->getName() . '"></table>';
        $html[] = '<div id="' . $grid->getOptions()->getPagerElementId() . '"></div>';

        return implode(PHP_EOL, $html);
    }

    /**
     * Render script of the grid
     */
    public function renderScript(Grid $grid): string
    {
        $filters = $grid->getParam('filters');

        $colNames = [];
        foreach ($grid->getColumns() as $column) {
            $label = $column->getAttributes()->getLabel();

            if (!empty($label)) {
                $label = $this->translate($label);
            }

            $colNames[] = $label;
        }

        $script[] = '    $(\'#' . $grid->getName() . '\').jqGrid({';
        $script[] = '        ' . $this->buildScript('grid', $grid->getOptions()->toArray()) . ', ' . PHP_EOL;

        // Vychozi filtry a ulozene filtry
        $rules = [];
        if (empty($filters['rules']) || empty($filters['operator'])) {
            foreach ($grid->getColumns() as $column) {
                $searchOptions = $column->getAttributes()->getSearchOptions();

                if (isset($searchOptions['defaultValue'])) {
                    $searchOperatos = $column->getAttributes()->getSearchOperators();

                    $searchOperator = 'cn';
                    if (!empty($searchOperatos)) {
                        $searchOperator = current($searchOperatos);
                    }
                    $searchOperatorMark = $grid->getFilterOperator($searchOperator);

                    $rules[] = [
                        'field' => $column->getName(),
                        'op' => $searchOperator,
                        'data' => $searchOptions['defaultValue'],
                    ];

                    /** @var \Lemo\JqGrid\ColumnInterface $column */
                    $column->getAttributes()->setSearchDataInit("function(elem) {
                        $(elem).val('" . addslashes((string) $searchOptions['defaultValue']) . "');
                        $(elem).parents('tr').find(\"[colname = '{$column->getName()}']\").attr('soper', '$searchOperator').text('$searchOperatorMark');
                    }");
                }
            }
        } else {
            foreach ($filters['rules'] as $field => $rule) {
                foreach ($rule as $filterDefinition) {
                    $rules[] = [
                        'field' => $field,
                        'op' => $grid->getFilterOperatorOutput($filterDefinition['operator']),
                        'data'  => $filterDefinition['value'],
                    ];
                }
            }
        }

        if (!is_array($filters) || !array_key_exists('operator', $filters)) {
            $filters['operator'] = 'AND';
        }
        $groupOp = strtoupper((string) $filters['operator']);
        if (!in_array($filters['operator'], ['AND', 'OR'])) {
            $groupOp = 'AND';
        }

        if (!empty($rules)) {
            $postData = [
                'filters' => [
                    'groupOp' => $groupOp,
                    'rules' => $rules,
                ]
            ];
            $script[] = '        postData: ' . json_encode($postData, JSON_THROW_ON_ERROR) . ',' . PHP_EOL;
        }

        $script[] = '        colNames: [\'' . implode('\', \'', $colNames) . '\'],';
        $script[] = '        colModel: [';

        $i = 1;
        $columns = $grid->getColumns();
        $columnsCount = count($columns);
        foreach ($columns as $column) {
            $attributes = $this->columnModifyAttributes($column, $column->getAttributes());

            if($i != $columnsCount) {
                $delimiter = ',';
            } else {
                $delimiter = '';
            }
            $script[] = '            {' . $this->buildScript('column', $attributes->toArray()) . '}' . $delimiter;
            $i++;
        }

        $script[] = '        ],';

        // RESIZE
        if (null !== $grid->getOptions()->getResizeCallback()) {
            $script[] = '        resizeStop: function(width, index) {
                ' . $grid->getOptions()->getResizeCallback() . '(this, width, index);
            },';
        }

        // LOAD COMPLETE
        if (null !== $grid->getColumnStyles() || null !== $grid->getRowStyles()) {
            $script[] = "        loadComplete: function(data) {";
            $script[] = "            var rows = $(this).getDataIDs();";

            if (null !== $grid->getColumnStyles()) {
                $script[] = "            var colModel = $(this).jqGrid('getGridParam', 'colModel');";
                $script[] = "            var colIndexes = new Array();";
                $script[] = "            $(colModel).each(function(i, col) {";
                $script[] = "                colIndexes[col.name] = i;";
                $script[] = "            });";
            }

            $script[] = "            for (var i = 0; i < rows.length; i++) {";
            if (null !== $grid->getColumnStyles()) {
                foreach ($grid->getColumnStyles() as $columnStyle) {
                    $script[] = $this->buildStyle($columnStyle);
                }
            }
            if (null !== $grid->getRowStyles()) {
                foreach ($grid->getRowStyles() as $rowStyle) {
                    $script[] = $this->buildStyle($rowStyle);
                }
            }
            $script[] = "            }";
            $script[] = "        }";
        }

        $script[] = '    });';
        $script[] = '    $(\'#' . $grid->getName() . '_pager option[value=-1]\').text(\'' . $this->translate('All') . '\');' . PHP_EOL;

        // Enable Advance search
        $buttonSearch = 'false';
        if ($grid->getOptions()->getAdvancedSearch()) {
            $buttonSearch = 'true';
        }

        // Can render toolbar?
        if ($grid->getOptions()->getFilterToolbarEnabled()) {
            $script[] = '    $(\'#' . $grid->getName() . '\').jqGrid(' . $this->buildScriptAttributes('filterToolbar', $grid->getOptions()->getFilterToolbar()) . ');' . PHP_EOL;
        }

        $script[] = "    $('#" . $grid->getName() . "').jqGrid('navGrid', '#" . $grid->getOptions()->getPagerElementId() . "', {del:false, add:false, edit:false, search:" . $buttonSearch . ", refresh:false},{},{},{},{multipleSearch:" . $buttonSearch . "});";

        $buttons = $grid->getButtons();
        if (!empty($buttons)) {
            foreach ($buttons as $button) {
                $script[] = "    $('#" . $grid->getName() . "').jqGrid('navButtonAdd', '#" . $grid->getName() . "_pager', {
                    caption: '" . $button['label'] . "',
                    buttonicon: '" . $button['icon'] . "',
                    onClickButton : function () {
                        " . $button['callback'] . "(this, '" . $grid->getName() . "');
                    }
                })";
            }
        }

        // REMAP
        if (null !== $grid->getOptions()->getRemapCallback()) {
            $script[] = "    $('#" . $grid->getName() . "').on('jqGridRemapColumns', function(e, indexes) {
                " . $grid->getOptions()->getRemapCallback() . "(this, indexes);
            });";
        }

        return implode(PHP_EOL, $script);
    }

    /**
     * Render script of grid
     */
    public function renderScriptAutoresize(Grid $grid): string
    {
        $script = [];
        $script[] = '    $(window).bind(\'resize\', function() {';
        $script[] = '        $(\'#' . $grid->getName() . '\').setGridWidth($(\'#gbox_' . $grid->getName() . '\').parent().width());';
        $script[] = '    }).trigger(\'resize\');';

        return implode(PHP_EOL, $script);
    }

    /**
     * Render script of attributes
     */
    protected function buildScript(string $type, array $attributes): string
    {
        $script = [];

        $separator = PHP_EOL;
        foreach ($attributes as $key => $value) {
            if (null === $value) {
                continue;
            }

            if ('grid' === $type) {
                if (!array_key_exists($key, $this->gridAttributes)) {
                    continue;
                }

                $key = $this->gridConvertAttributeName($key);
                $separator = ', ' . PHP_EOL;
            }
            if ('column' === $type) {
                if (!array_key_exists($key, $this->columnAttributes)) {
                    continue;
                }

                $key = $this->columnConvertAttributeName($key);
                $separator = ', ';
            }

            $scriptRow = $this->buildScriptAttributes($key, $value);

            if (null !== $scriptRow) {
                $script[] = $scriptRow;
            }
        }

        return implode($separator, $script);
    }

    protected function buildScriptAttributes(mixed $key, mixed $value): int|string|null
    {
        if ('hidedlg' === $key) {
            if ($value == true) {
                $value = false;
            } else {
                $value = true;
            }
        }

        if (is_array($value)) {
            if (empty($value)) {
                return null;
            }

            if ($key === 'value') {
                $values = [];
                foreach($value as $k => $val) {
                    $values[] = $k . ':' . $val;
                }

                return 'value: "' . implode(';', $values) . '"';
            }

            $values = [];
            foreach ($value as $k => $val) {
                if ('defaultValue' === $k && 'searchoptions' === $key) {
                    continue;
                }

                if (is_int($k)) {
                    if ('rowList' === $key) {
                        $values[] = $val;
                    } else {
                        $values[] = "'" . $val . "'";
                    }
                } else {
                    $da = $this->buildScriptAttributes($k, $val);

                    if (!empty($da)) {
                        $values[] = $this->buildScriptAttributes($k, $val);
                    }
                }
            }

            if ('filterToolbar' === $key) {
                $r = '\'' . $key . '\', {' . implode(', ', array_values($values)) . '}';
            } elseif (in_array($key, ['editoptions', 'formatoptions', 'groupingView', 'searchoptions', 'treeicons'])) {
                $r = $key . ': {' . implode(', ', $values) . '}';
            } else {
                $r = $key . ': [' . implode(', ', $values) . ']';
            }
        } elseif ('groupSummary' === $key) {
            $r = $key . ': [' . $value . ']';
        } elseif (is_numeric($key)) {
            if (is_bool($value)) {
                if ($value == true) {
                    $value = 'true';
                } else {
                    $value = 'false';
                }
            } elseif (is_numeric($value)) {
                $value = $value;
            } else {
                $value = '\'' . $value . '\'';
            }

            $r = $value;
        } elseif (is_numeric($value)) {
            $r = $key . ': ' . $value;
        } elseif (is_bool($value)) {
            if ($value == true) {
                $value = 'true';
            } else {
                $value = 'false';
            }
            $r = $key . ': ' . $value;
        } elseif ('dataInit' === $key) {
            $r = $key . ': ' . $value;
        } else {
            $r = $key . ': \'' . $value . '\'';
        }

        return $r;
    }

    /**
     * Convert attribute name to jqGrid attribute name
     */
    protected function columnConvertAttributeName(string $name): string
    {
        if (array_key_exists($name, $this->columnAttributes)) {
            $name = (string) $this->columnAttributes[$name];
        }

        return strtolower($name);
    }

    /**
     * Add, update or remove some column attributes
     */
    protected function columnModifyAttributes(ColumnInterface $column, ColumnAttributes $attributes): ColumnAttributes
    {
        if (null === $attributes->getName()) {
            $attributes->setName($column->getName());
        }

        return $attributes;
    }

    /**
     * Convert attribute name to jqGrid attribute name
     */
    protected function gridConvertAttributeName(string $name): string
    {
        if (array_key_exists($name, $this->gridAttributes)) {
            $name = $this->gridAttributes[$name];
        }

        return $name;
    }

    /**
     * Modify grid attributes before rendering
     */
    protected function gridModifyAttributes(Grid $grid): GridOptions
    {
        $attributes = $grid->getOptions();

        // Pager element ID
        if (null === $attributes->getPagerElementId()) {
            $attributes->setPagerElementId($grid->getName() . '_pager');
        }

        // Number of visible pages
        $numberOfVisibleRows = $grid->getNumberOfVisibleRows();
        $attributes->setRecordsPerPage($numberOfVisibleRows);

        // Number of current page
        $numberOfCurrentPage = $grid->getNumberOfCurrentPage();
        $attributes->setPage($numberOfCurrentPage);

        // Sorting
        $sort = $grid->getSort();

        $sidx = '';
        $sord = '';
        $sortCount = count($sort);
        $i = 0;
        foreach ($sort as $column => $direct) {
            $i++;

            if (1 == $sortCount) {
                $sidx = $column;
                $sord = $direct;
            } elseif ($i == $sortCount) {
                $sidx .= $column;
                $sord = $direct;
            } else {
                $sidx .= $column . ' ' . $direct . ', ';
            }
        }

        if (!empty($sidx) || !empty($sord)) {
            $attributes->setSortName($sidx);
            $attributes->setSortOrder($sord);
        }

        $attributes->setUrl($this->buildUrl($grid));

        return $attributes;
    }

    protected function buildUrl(Grid $grid): string
    {
        $attributes = $grid->getOptions();

        // URL
        $url = $attributes->getUrl();
        if (empty($url)) {
            $url = (string) $_SERVER['REQUEST_URI'];
        }
        $url = parse_url($url);

        $queryParams = [];
        if (isset($url['query'])) {
            parse_str($url['query'], $queryParams);
        }

        $queryParams['_name'] = $grid->getName();

        return $url['path'] . '?' . http_build_query($queryParams);
    }

    protected function buildStyle(ColumnStyle|RowStyle $style): string
    {
        // Build condition string
        $conditions = [];
        foreach ($style->getConditions() as $condition) {
            $conditions[] = "$(this).getCell(rows[i], '" . $condition->getColumn() . "') " . $condition->getOperator() . " '" . $condition->getValue() . "'";
        }
        $conditions = implode(' && ', $conditions);

        // Build properties string
        $properties = [];
        foreach ($style->getProperties() as $property) {
            $properties[] = "'" . $property->getName() . "': '" . $property->getValue() . "'";
        }
        $properties = implode(', ', $properties);

        $script = [];

        // Condition - Start
        if (!empty($style->getConditions())) {
            $script[] = "                if(" . $conditions . ") {";
        }

        // Properties
        if ($style instanceof ColumnStyle) {
            $script[] = "                    $(this).jqGrid('setCell', rows[i], colIndexes['" . $style->getColumn() . "'], '', {" . $properties . "});";
        } else {
            $script[] = "                    $(this).jqGrid('setRowData', rows[i], false, {" . $properties . "});";
        }

        // Condition - End
        if (!empty($style->getConditions())) {
            $script[] = "                }";
        }

        return implode(PHP_EOL, $script);
    }

    private function translate(string $message): string
    {
        if (null !== $this->translator) {
            return $this->translator->translate($message);
        }

        return $message;
    }
}
