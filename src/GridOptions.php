<?php

namespace Lemo\JqGrid;

use Laminas\Stdlib\AbstractOptions;

use function in_array;
use function strtolower;

class GridOptions extends AbstractOptions
{
    /**
     * Request types
     */
    final public const REQUEST_TYPE_GET  = 'get';
    final public const REQUEST_TYPE_POST = 'post';

    /**
     * Sort orders
     */
    final public const SORT_ORDER_ASC  = 'asc';
    final public const SORT_ORDER_DESC = 'desc';

    /**
     * Is advanced search enabled?
     */
    protected ?bool $advancedSearch = null;

    /**
     * Set a zebra-striped grid.
     */
    protected ?bool $alternativeRows = null;

    /**
     * The class that is used for alternate (zebra) rows. You can construct your own class and replace this value.
     * This option is valid only if altRows options is set to true.
     */
    protected ?string $alternativeRowsClass = null;

    /**
     * When set to true encodes (html encode) the incoming (from server) and posted data (from editing modules).
     * By example < will be converted to &lt;
     */
    protected ?bool $autoEncodeIncomingAndPostData = null;

    /**
     * When set to true, the grid width is recalculated automatically to the width of the parent element. This is done
     * only initially when the grid is created. In order to resize the grid when the parent element changes width you
     * should apply custom code and use the setGridWidth method for this purpose.
     */
    protected ?bool $autowidth = null;

    /**
     * Defines the Caption layer for the grid. This caption appears above the Header layer. If the string is empty
     * the caption does not appear.
     */
    protected ?string $caption = null;

    /**
     * Enables (disables) cell editing. See Cell Editing for more details.
     */
    protected ?bool $cellEdit = null;

    /**
     * Defines the url for inline and form editing.
     */
    protected ?string $cellEditUrl = null;

    /**
     * This option determines the padding + border width of the cell. Usually this should not be changed, but if custom
     * changes to td element are made in the grid css file this will need to be changed. The initial value of 5 means
     * paddingLef?2+paddingRight?2+borderLeft?1=5.
     */
    protected ?int $cellLayout = null;

    /**
     * Determines where the contents of the cell are saved: 'remote' or 'clientArray'.
     */
    protected ?string $cellSaveType = null;

    /**
     * The url where the cell is to be saved.
     */
    protected ?string $cellSaveUrl = null;

    /**
     * Is column chooser enabled?
     */
    protected ?bool $columnChooser = null;

    /**
     * A array that store the local data passed to the grid. You can directly point to this variable in case you want
     * to load a array data. It can replace addRowData method which is slow on relative big data.
     */
    protected ?array $data = null;

    protected ?string $dataString = null;

    /**
     * Defines what type of information to expect to represent data in the grid. Valid options are xml - we expect
     * xml data; xmlstring - we expect xml data as string; json - we expect JSON data; jsonstring - we expect JSON data
     * as string; local - we expect data defined at client side (array data); javascript - we expect javascript as data;
     * function - custom defined function for retrieving data.
     */
    protected string $dataType = 'json';

    /**
     * Enables grouping in grid.
     */
    protected ?bool $grouping = null;

    /**
     * Indicates which column should be used to expand the tree grid. If not set the first one
     * is used. Valid only when treeGrid option is set to true.
     */
    protected ?string $expandColumnIdentifier = null;

    /**
     * When true, the treeGrid is expanded and/or collapsed when we click on the text of the expanded column, not
     * only on the image
     */
    protected ?bool $expandColumnOnClick = null;

    protected array $filterToolbar = [
        'enableClear' => true,
        'stringResult' => true,
        'searchOnEnter' => true,
        'searchOperators' => true,
    ];

    /**
     * Is filter toolbat enabled?
     */
    protected ?bool $filterToolbarEnabled = true;

    /**
     * If set to true, and resizing the width of a column, the adjacent column (to the right) will resize so that
     * the overall grid width is maintained (e.g., reducing the width of column 2 by 30px will increase the size of
     * column 3 by 30px). In this case there is no horizontal scrolbar. Note: this option is not compatible with
     * shrinkToFit option - i.e if shrinkToFit is set to false, forceFit is ignored.
     */
    protected ?bool $forceFit = null;

    /**
     * Determines the current state of the grid (i.e. when used with hiddengrid, hidegrid and caption options). Can
     * have either of two states: 'visible' or 'hidden'
     */
    protected ?String $gridState = null;

    /**
     * In the previous versions of jqGrid including 3.4.X, reading a relatively large data set (number of rows >= 100)
     * caused speed problems. The reason for this was that as every cell was inserted into the grid we applied about
     * 5 to 6 jQuery calls to it. Now this problem is resolved; we now insert the entry row at once with a jQuery
     * append. The result is impressive - about 3 to 5 times faster. What will be the result if we insert all the data
     * at once? Yes, this can be done with a help of gridview option (set it to true). The result is a grid that is
     * 5 to 10 times faster. Of course, when this option is set to true we have some limitations. If set to true we can
     * not use treeGrid, subGrid, or the afterInsertRow event. If you do not use these three options in the grid you can
     * set this option to true and enjoy the speed.
     */
    protected ?bool $gridView = true;

    /**
     * If the option is set to true the title attribute is added to the column headers.
     */
    protected ?string $headerTitles = null;

    /**
     * The height of the grid. Can be set as number (in this case we mean pixels) or as percentage
     * (only 100% is acceped) or value of auto is acceptable.
     */
    protected ?string $height = '100%';

    /**
     * When set to false the effect of mouse hovering over the grid data rows is disabled.
     */
    protected ?bool $hoverRows = null;

    protected ?string $loadDataCallback = null;

    /**
     * If this flag is set to true, the grid loads the data from the server only once (using the appropriate datatype).
     * After the first request the datatype parameter is automatically changed to local and all further manipulations
     * are done on the client side. The functions of the pager (if present) are disabled.
     */
    protected ?bool $loadOnce = null;

    /**
     * This option controls what to do when an ajax operation is in progress.
     * 'disable', 'enable' or 'block'
     */
    protected ?string $loadType = 'disable';

    /**
     * If this flag is set to true a multi selection of rows is enabled. A new column at left side is added. Can be used
     * with any datatype option.
     */
    protected ?bool $multiSelect = null;

    /**
     * This parameter have sense only multiselect option is set to true. Defines the key which will be pressed when we
     * make multiselection. The possible values are: shiftKey - the user should press Shift Key altKey - the user should
     * press Alt Key ctrlKey - the user should press Ctrl Key
     *
     * 'shiftKey', 'altKey', 'ctrlKey'
     */
    protected ?string $multiSelectKey = null;

    /**
     * Determines the width of the multiselect column if multiselect is set to true.
     */
    protected ?int $multiSelectWidth = null;

    /**
     * If set to true enables the multisorting. The options work if the datatype is local. In case when the data is
     * obtained from the server the sidx parameter contain the order clause. It is a comma separated string in format
     * field1 asc, field2 desc …, fieldN. Note that the last field does not not have asc or desc. It should be obtained
     * from sord parameter. When the option is true the behavior is a s follow. The first click of the header field sort
     * the field depending on the firstsortoption parameter in colModel or sortorder grid parameter. The next click sort
     * it in reverse order. The third click removes the sorting from this field
     */
    protected ?bool $multiSort = null;

    /**
     * The initial page number when we make the request.This parameter is passed to the url for use by the server
     * routine retrieving the data.
     */
    protected int $page = 1;

    /**
     * Defines that we want to use a pager bar to navigate through the records. This must be a valid html element;
     * in our example we gave the div the id of “pager”, but any name is acceptable. Note that the Navigation layer
     * (the “pager” div) can be positioned anywhere you want, determined by your html; in our example we specified that
     * the pager will appear after the Table Body layer.
     */
    protected ?string $pagerElementId = null;

    /**
     * Determines the position of the pager in the grid. By default the pager element when created is divided in 3 parts
     * (one part for pager, one part for navigator buttons and one part for record information)
     *
     * 'left', 'center' or 'right'
     */
    protected ?string $pagerPosition = null;

    /**
     * Determines if the Pager buttons should be shown if pager is available. Also valid only if pager is set correctly.
     * The buttons are placed in the pager bar.
     */
    protected ?bool $pagerShowButtons = null;

    /**
     * Determines if the input box, where the user can change the number of requested page, should be available.
     * The input box appear in the pager bar.
     */
    protected ?bool $pagerShowInput = null;

    /**
     * Determines the position of the record information in the pager.
     *
     * 'left', 'center' or 'right'
     */
    protected ?string $recordPosition = null;

    protected ?int $recordsPerPage = 20;

    /**
     * Defines the type of request to make ('post' or 'get')
     */
    protected ?string $requestType = null;

    protected ?string $remapCallback = null;

    /**
     * If set to true this will place a footer table with one row below the gird records and above the pager.
     */
    protected ?bool $renderFooterRow = null;

    /**
     * Enables or disables the show/hide grid button, which appears on the right side of the Caption layer. Takes effect
     * only if the caption property is not an empty string.
     */
    protected ?bool $renderHideGridButton = null;

    /**
     * If true, jqGrid displays the beginning and ending record number in the grid, out of the total number of records
     * in the query. This information is shown in the pager bar (bottom right by default)in this format:
     * “View X to Y out of Z”. If this value is true, there are other parameters that can be adjusted,
     * including 'emptyrecords' and 'recordtext'.
     */
    protected ?bool $renderRecordsInfo = true;

    /**
     * If this option is set to true, a new column at left of the grid is added. The purpose of this column is to count
     * the number of available rows, beginning from 1. In this case colModel is extended automatically with new element
     * with name - 'rn'. Also, be careful not to use the name 'rn'.
     */
    protected ?bool $renderRowNumbersColumn = null;

    /**
     * Assigns a class to columns that are resizable so that we can show a resize handle only for ones that are
     * resizable.
     */
    protected ?string $resizeClass = null;

    protected ?string $resizeCallback = null;

    /**
     * Sets how many records we want to view in the grid. This parameter is passed to the url for use by the server
     * routine retrieving the data. Note that if you set this parameter to 10 (i.e. retrieve 10 records) and your server
     * return 15 then only 10 records will be loaded.
     *
    protected int $recordsPerPage = 15;

    /**
     * An array to construct a select box element in the pager in which we can change the number of the visible rows.
     * When changed during the execution, this parameter replaces the rowNum parameter that is passed to the url.
     * If the array is empty the element does not appear in the pager. Typical you can set this like [10,20,30].
     * If the rowNum parameter is set to 30 then the selected value in the select box is 30.
     */
    protected array $recordsPerPageList = [5, 10, 20, 50, 100];

    /**
     * Column name to be used as rowId instead of row index ('id', 'uuid' etc.)
     */
    protected ?string $rowIdColumn = null;

    /**
     * Creates dynamic scrolling grids. When enabled, the pager elements are disabled and we can use the vertical
     * scrollbar to load data. When set to true the grid will always hold all the items from the start through to the
     * latest point ever visited. When scroll is set to value (eg 1), the grid will just hold the visible lines. This
     * allow us to load the data at portions whitout to care about the memory leaks. Additionally this we have optional
     * extension to the server protocol: npage (see prmNames array). If you set the npage option in prmNames, then the
     * grid will sometimes request more than one page at a time, if not it will just perform multiple gets.
     */
    protected bool|int|null $scroll = null;

    /**
     * Determines the width of the vertical scrollbar. Since different browsers interpret this width differently
     * (and it is difficult to calculate it in all browsers) this can be changed.
     */
    protected ?int $scrollOffset = null;

    /**
     * When enabled, selecting a row with setSelection scrolls the grid so that the selected row is visible. This is
     * especially useful when we have a verticall scrolling grid and we use form editing with navigation buttons
     * (next or previous row). On navigating to a hidden row, the grid scrolls so the selected row becomes visible.
     */
    protected ?bool $scrollRows = null;

    /**
     * This control the timeout handler when scroll is set to 1. In miliseconds.
     */
    protected ?int $scrollTimeout = null;

    /**
     * This option describes the type of calculation of the initial width of each column against with the width of the
     * grid. If the value is true and the value in width option is set then: Every column width is scaled according to
     * the defined option width. Example: if we define two columns with a width of 80 and 120 pixels, but want the grid
     * to have a 300 pixels - then the columns are recalculated as follow:
     * 1- column = 300(new width)/200(sum of all width)*80(column width) = 120 and 2 column = 300/200*120 = 180.
     *
     * The grid width is 300px. If the value is false and the value in width option is set then:
     * The width of the grid is the width set in option. The column width are not recalculated and have the values
     * defined in colModel. If integer is set, the width is calculated according to it.
     */
    protected ?bool $shrinkToFit = null;

    /**
     * The column according to which the data is to be sorted when it is initially loaded from the server (note that you
     * will have to use datatypes xml or json to load remote data). This parameter is appended to the url. If this value
     * is set and the index (name) matches the name from colModel, then an icon indicating that the grid is sorted
     * according to this column is added to the column header.
     */
    protected ?string $sortName = null;

    /**
     * The initial sorting order (ascending or descending) when we fetch data from the server using datatypes xml or
     * json. This parameter is appended to the url - see prnNames. The two allowed values are - asc or desc.
     */
    protected string $sortOrder = self::SORT_ORDER_ASC;

    /**
     * When enabled this option allow column reordering with mouse. Since this option uses jQuery UI sortable widget,
     * be a sure that this widget and the related to widget files are loaded in head tag. Also be a sure too that you
     * mark the grid.jqueryui.js when you download the jqGrid.
     */
    protected ?bool $sortingColumns = null;

    /**
     * The purpose of this parameter is to define different look and behavior of sorting icons that appear near the header.
     * This parameter is array with the following default options viewsortcols : [false,'vertical',true].
     *
     * The first parameter determines if all icons should be viewed at the same time when all columns have sort property
     * set to true. The default of false determines that only the icons of the current sorting column should be viewed.
     * Setting this parameter to true causes all icons in all sortable columns to be viewed.
     *
     * The second parameter determines how icons should be placed - vertical means that the sorting icons are one under
     * another. 'horizontal' means that the icons should be one near other.
     *
     * The third parameter determines the click functionality. If set to true the columns are sorted if the header is
     * clicked. If set to false the columns are sorted only when the icons are clicked.
     *
     * Important note: When set a third parameter to false be a sure that the first parameter is set to true, otherwise
     * you will loose the sorting.
     */
    protected array $sortingColumnsDefinition = [true, 'vertical', true];

    /**
     * Enables (disables) the tree grid format.
     */
    protected ?bool $treeGrid = null;

    /**
     * Deteremines the method used for the treeGrid. Can be 'nested'
     * or 'adjacency'
     */
    protected ?string $treeGridType = null;

    /**
     * This array set the icons used in the tree. The icons should be a valid
     * names from UI theme roller images.
     * The default values are:
     *
     * array(
     *  'plus' => 'ui-icon-triangle-1-e',
     *  'minus' => 'ui-icon-triangle-1-s',
     *  'leaf' => 'ui-icon-radio-off'
     * );
     */
    protected ?array $treeGridIcons = null;

    /**
     * The url of the file that holds the request
     */
    protected ?string $url = null;

    /**
     * This array contains custom information from the request. Can be used at
     * any time.
     */
    protected array $userData = [];

    /**
     * When set to true we directly place the user data array userData in
     * the footer. The rules are as follows: If the userData array contains
     * a name which matches any name defined in colModel, then the value is
     * placed in that column. If there are no such values nothing is placed.
     * Note that if this option is used we use the current formatter options
     * (if available) for that column.
     */
    protected ?bool $userDataOnFooter = null;

    /**
     * If this option is not set, the width of the grid is a sum of the widths
     * of the columns defined (in pixels).
     * If this option is set, the initial width of each column is set according
     * to the value of shrinkToFit option.
     */
    protected ?int $width = null;

    public function setAdvancedSearch(?bool $advancedSearch): self
    {
        $this->advancedSearch = $advancedSearch;

        return $this;
    }

    public function getAdvancedSearch(): ?bool
    {
        return $this->advancedSearch;
    }

    public function setAlternativeRows(?bool $alternativeRows): self
    {
        $this->alternativeRows = $alternativeRows;

        return $this;
    }

    public function getAlternativeRows(): ?bool
    {
        return $this->alternativeRows;
    }

    public function setAlternativeRowsClass(?string $alternativeRowsClass): self
    {
        $this->alternativeRowsClass = $alternativeRowsClass;

        return $this;
    }

    public function getAlternativeRowsClass(): ?string
    {
        return $this->alternativeRowsClass;
    }

    public function setAutoEncodeIncomingAndPostData(?bool $autoEncodeIncomingAndPostData): self
    {
        $this->autoEncodeIncomingAndPostData = $autoEncodeIncomingAndPostData;

        return $this;
    }

    public function getAutoEncodeIncomingAndPostData(): ?bool
    {
        return $this->autoEncodeIncomingAndPostData;
    }

    public function setAutowidth(?bool $autowidth): self
    {
        $this->autowidth = $autowidth;

        return $this;
    }

    public function getAutowidth(): ?bool
    {
        return $this->autowidth;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCellEdit(?bool $cellEdit): self
    {
        $this->cellEdit = $cellEdit;

        return $this;
    }

    public function getCellEdit(): ?bool
    {
        return $this->cellEdit;
    }

    public function setCellEditUrl(?string $cellEditUrl): self
    {
        $this->cellEditUrl = $cellEditUrl;

        return $this;
    }

    public function getCellEditUrl(): ?string
    {
        return $this->cellEditUrl;
    }

    public function setCellLayout(?int $cellLayout): self
    {
        $this->cellLayout = $cellLayout;

        return $this;
    }

    public function getCellLayout(): ?int
    {
        return $this->cellLayout;
    }

    public function setCellSaveType(?string $cellSaveType): self
    {
        $this->cellSaveType = $cellSaveType;

        return $this;
    }

    public function getCellSaveType(): ?string
    {
        return $this->cellSaveType;
    }

    public function setCellSaveUrl(?string $cellSaveUrl): self
    {
        $this->cellSaveUrl = $cellSaveUrl;

        return $this;
    }

    public function getCellSaveUrl(): ?string
    {
        return $this->cellSaveUrl;
    }

    public function setColumnChooser(?bool $columnChooser): self
    {
        $this->columnChooser = $columnChooser;

        return $this;
    }

    public function getColumnChooser(): ?bool
    {
        return $this->columnChooser;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setDataString(?string $dataString): self
    {
        $this->dataString = $dataString;

        return $this;
    }

    public function getDataString(): ?string
    {
        return $this->dataString;
    }

    public function setDataType(?string $dataType): self
    {
        $this->dataType = $dataType;

        return $this;
    }

    public function getDataType(): ?string
    {
        return $this->dataType;
    }

    public function setExpandColumnIdentifier(?string $expandColumnIdentifier): self
    {
        $this->expandColumnIdentifier = $expandColumnIdentifier;

        return $this;
    }

    public function getExpandColumnIdentifier(): ?string
    {
        return $this->expandColumnIdentifier;
    }

    public function setExpandColumnOnClick(?bool $expandColumnOnClick): self
    {
        $this->expandColumnOnClick = $expandColumnOnClick;

        return $this;
    }

    public function getExpandColumnOnClick(): ?bool
    {
        return $this->expandColumnOnClick;
    }

    public function setFilterToolbar(bool $searchOnEnter, bool $showOperators): self
    {
        $this->filterToolbar = [
            'stringResult' => true,
            'searchOnEnter' => $searchOnEnter,
            'searchOperators' => $showOperators,
        ];

        return $this;
    }

    public function getFilterToolbar(): array
    {
        return $this->filterToolbar;
    }

    /**
     * Set filter toolbar enable clear
     */
    public function setFilterToolbarEnableClear(bool $enableClear): self
    {
        $this->filterToolbar['enableClear'] = $enableClear;

        return $this;
    }

    /**
     * Filter toolbar enable clear?
     */
    public function getFilterToolbarEnableClear(): bool
    {
        return $this->filterToolbar['enableClear'];
    }

    /**
     * Set if filter toolbar search only on Enter
     */
    public function setFilterToolbarSearchOnEnter(bool $searchOnEnter): self
    {
        $this->filterToolbar['searchOnEnter'] = $searchOnEnter;

        return $this;
    }

    /**
     * Filter toolbar only on Enter?
     */
    public function getFilterToolbarSearchOnEnter(): bool
    {
        return $this->filterToolbar['searchOnEnter'];
    }

    /**
     * Set if filter toolbar show operators
     */
    public function setFilterToolbarShowOperators(bool $showOperators): self
    {
        $this->filterToolbar['searchOperators'] = $showOperators;

        return $this;
    }

    /**
     * Show operators in filer toolbar?
     */
    public function getFilterToolbarShowOperators(): bool
    {
        return $this->filterToolbar['searchOperators'] ?? false;
    }

    public function setFilterToolbarEnabled(?bool $filterToolbarEnabled): self
    {
        $this->filterToolbarEnabled = $filterToolbarEnabled;

        return $this;
    }

    public function getFilterToolbarEnabled(): ?bool
    {
        return $this->filterToolbarEnabled;
    }

    public function setForceFit(?bool $forceFit): self
    {
        $this->forceFit = $forceFit;

        return $this;
    }

    public function getForceFit(): ?bool
    {
        return $this->forceFit;
    }

    public function setGridState(?string $gridState): self
    {
        $this->gridState = $gridState;

        return $this;
    }

    public function getGridState(): ?string
    {
        return $this->gridState;
    }

    public function setGridView(?bool $gridView): self
    {
        $this->gridView = $gridView;

        return $this;
    }

    public function getGridView(): ?bool
    {
        return $this->gridView;
    }

    public function setGrouping(?bool $grouping): self
    {
        $this->grouping = $grouping;

        return $this;
    }

    public function getGrouping(): ?bool
    {
        return $this->grouping;
    }

    public function setHeaderTitles(?string $headerTitles): self
    {
        $this->headerTitles = $headerTitles;

        return $this;
    }

    public function getHeaderTitles(): ?string
    {
        return $this->headerTitles;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHoverRows(?bool $hoverRows): self
    {
        $this->hoverRows = $hoverRows;

        return $this;
    }

    public function getHoverRows(): ?bool
    {
        return $this->hoverRows;
    }

    public function setLoadDataCallback(?string $loadDataCallback): self
    {
        $this->loadDataCallback = $loadDataCallback;

        return $this;
    }

    public function getLoadDataCallback(): ?string
    {
        return $this->loadDataCallback;
    }

    public function setLoadOnce(?bool $loadOnce): self
    {
        $this->loadOnce = $loadOnce;

        return $this;
    }

    public function getLoadOnce(): ?bool
    {
        return $this->loadOnce;
    }

    public function setLoadType(?string $loadType): self
    {
        $this->loadType = $loadType;

        return $this;
    }

    public function getLoadType(): ?string
    {
        return $this->loadType;
    }

    public function setMultiSelect(?bool $multiSelect): self
    {
        $this->multiSelect = $multiSelect;

        return $this;
    }

    public function getMultiSelect(): ?bool
    {
        return $this->multiSelect;
    }

    public function setMultiSelectKey(?string $multiSelectKey): self
    {
        $this->multiSelectKey = $multiSelectKey;

        return $this;
    }

    public function getMultiSelectKey(): ?string
    {
        return $this->multiSelectKey;
    }

    public function setMultiSelectWidth(?int $multiSelectWidth): self
    {
        $this->multiSelectWidth = $multiSelectWidth;

        return $this;
    }

    public function getMultiSelectWidth(): ?int
    {
        return $this->multiSelectWidth;
    }

    public function setMultiSort(?bool $multiSort): self
    {
        $this->multiSort = $multiSort;

        return $this;
    }

    public function getMultiSort(): ?bool
    {
        return $this->multiSort;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPagerElementId(?string $pagerElementId): self
    {
        $this->pagerElementId = $pagerElementId;

        return $this;
    }

    public function getPagerElementId(): ?string
    {
        return $this->pagerElementId;
    }

    public function setPagerPosition(?string $pagerPosition): self
    {
        $this->pagerPosition = $pagerPosition;

        return $this;
    }

    public function getPagerPosition(): ?string
    {
        return $this->pagerPosition;
    }

    public function setPagerShowButtons(?bool $pagerShowButtons): self
    {
        $this->pagerShowButtons = $pagerShowButtons;

        return $this;
    }

    public function getPagerShowButtons(): ?bool
    {
        return $this->pagerShowButtons;
    }

    public function setPagerShowInput(?bool $pagerShowInput): self
    {
        $this->pagerShowInput = $pagerShowInput;

        return $this;
    }

    public function getPagerShowInput(): ?bool
    {
        return $this->pagerShowInput;
    }

    public function setRecordPosition(?string $recordPosition): self
    {
        $this->recordPosition = $recordPosition;

        return $this;
    }

    public function getRecordPosition(): ?string
    {
        return $this->recordPosition;
    }

    public function setRecordsPerPage(?int $recordsPerPage): self
    {
        $this->recordsPerPage = $recordsPerPage;

        return $this;
    }

    public function getRecordsPerPage(): ?int
    {
        return $this->recordsPerPage;
    }

    public function setRecordsPerPageList(array $recordsPerPageList): self
    {
        $this->recordsPerPageList = $recordsPerPageList;

        return $this;
    }

    public function getRecordsPerPageList(): array
    {
        return $this->recordsPerPageList;
    }

    public function setRowIdColumn(?string $rowIdColumn): self
    {
        $this->rowIdColumn = $rowIdColumn;

        return $this;
    }

    public function getRowIdColumn(): ?string
    {
        return $this->rowIdColumn;
    }

    public function setRemapCallback(?string $remapCallback): self
    {
        $this->remapCallback = $remapCallback;

        return $this;
    }

    public function getRemapCallback(): ?string
    {
        return $this->remapCallback;
    }

    public function setRenderFooterRow(?bool $renderFooterRow): self
    {
        $this->renderFooterRow = $renderFooterRow;

        return $this;
    }

    public function getRenderFooterRow(): ?bool
    {
        return $this->renderFooterRow;
    }

    public function setRenderHideGridButton(?bool $renderHideGridButton): self
    {
        $this->renderHideGridButton = $renderHideGridButton;

        return $this;
    }

    public function getRenderHideGridButton(): ?bool
    {
        return $this->renderHideGridButton;
    }

    public function setRenderRecordsInfo(?bool $renderRecordsInfo): self
    {
        $this->renderRecordsInfo = $renderRecordsInfo;

        return $this;
    }

    public function getRenderRecordsInfo(): ?bool
    {
        return $this->renderRecordsInfo;
    }

    public function setRenderRowNumbersColumn(?bool $renderRowNumbersColumn): self
    {
        $this->renderRowNumbersColumn = $renderRowNumbersColumn;

        return $this;
    }

    public function getRenderRowNumbersColumn(): ?bool
    {
        return $this->renderRowNumbersColumn;
    }

    public function setRequestType(?string $requestType): self
    {
        $this->requestType = $requestType;

        return $this;
    }

    public function getRequestType(): ?string
    {
        return $this->requestType;
    }

    public function setResizeClass(?string $resizeClass): self
    {
        $this->resizeClass = $resizeClass;

        return $this;
    }

    public function getResizeClass(): ?string
    {
        return $this->resizeClass;
    }

    public function setResizeCallback(?string $resizeCallback): self
    {
        $this->resizeCallback = $resizeCallback;

        return $this;
    }

    public function getResizeCallback(): ?string
    {
        return $this->resizeCallback;
    }

    public function setScroll(bool|int|null $scroll): self
    {
        $this->scroll = $scroll;

        return $this;
    }

    public function getScroll(): bool|int|null
    {
        return $this->scroll;
    }

    public function setScrollOffset(?int $scrollOffset): self
    {
        $this->scrollOffset = $scrollOffset;

        return $this;
    }

    public function getScrollOffset(): ?int
    {
        return $this->scrollOffset;
    }

    public function setScrollRows(?bool $scrollRows): self
    {
        $this->scrollRows = $scrollRows;

        return $this;
    }

    public function getScrollRows(): ?bool
    {
        return $this->scrollRows;
    }

    public function setScrollTimeout(?int $scrollTimeout): self
    {
        $this->scrollTimeout = $scrollTimeout;

        return $this;
    }

    public function getScrollTimeout(): ?int
    {
        return $this->scrollTimeout;
    }

    public function setShrinkToFit(?bool $shrinkToFit): self
    {
        $this->shrinkToFit = $shrinkToFit;

        return $this;
    }

    public function getShrinkToFit(): ?bool
    {
        return $this->shrinkToFit;
    }

    public function setSortName(?string $sortName): self
    {
        $this->sortName = $sortName;

        return $this;
    }

    public function getSortName(): ?string
    {
        return $this->sortName;
    }

    /**
     *@throws Exception\InvalidArgumentException
     */
    public function setSortOrder(string $sortOrder): self
    {
        $order = strtolower($sortOrder);

        if (!in_array($order, [self::SORT_ORDER_ASC, self::SORT_ORDER_DESC])) {
            throw new Exception\InvalidArgumentException("Order must by 'asc' or 'desc'");
        }

        $this->sortOrder = $order;

        return $this;
    }

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    public function setSortingColumns(?bool $sortingColumns): self
    {
        $this->sortingColumns = $sortingColumns;

        return $this;
    }

    public function getSortingColumns(): ?bool
    {
        return $this->sortingColumns;
    }

    public function setSortingColumnsDefinition(array $sortingColumnsDefinition): self
    {
        $this->sortingColumnsDefinition = $sortingColumnsDefinition;

        return $this;
    }

    public function getSortingColumnsDefinition(): array
    {
        return $this->sortingColumnsDefinition;
    }

    public function setTreeGrid(?bool $treeGrid): self
    {
        $this->treeGrid = $treeGrid;

        return $this;
    }

    public function getTreeGrid(): ?bool
    {
        return $this->treeGrid;
    }

    /**
     * Default:
     *  - plus: ui-icon-triangle-1-e
     *  - minus: ui-icon-triangle-1-s
     *  - leaf: ui-icon-radio-off
     */
    public function setTreeGridIcons(string $plus, string $minus, string $leaf): self
    {
        $this->treeGridIcons = [
            'plus' => $plus,
            'minus' => $minus,
            'leaf' => $leaf
        ];

        return $this;
    }

    public function getTreeGridIcons(): ?array
    {
        return $this->treeGridIcons;
    }

    public function setTreeGridType(?string $treeGridType): self
    {
        $this->treeGridType = $treeGridType;

        return $this;
    }

    public function getTreeGridType(): ?string
    {
        return $this->treeGridType;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUserData(array $userData): self
    {
        $this->userData = $userData;

        return $this;
    }

    public function getUserData(): array
    {
        return $this->userData;
    }

    public function setUserDataOnFooter(?bool $userDataOnFooter): self
    {
        $this->userDataOnFooter = $userDataOnFooter;

        return $this;
    }

    public function getUserDataOnFooter(): ?bool
    {
        return $this->userDataOnFooter;
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
