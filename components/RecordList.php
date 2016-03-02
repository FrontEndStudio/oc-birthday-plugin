<?php namespace Fes\Birthday\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Lang;
use Exception;
use SystemException;

class RecordList extends ComponentBase
{
    /**
    * A collection of records to display
    * @var Collection
    */
    public $records;

    /**
    * Message to display when there are no records.
    * @var string
    */
    public $noRecordsMessage;

    /**
    * Reference to the page name for linking to details.
    * @var string
    */
    public $detailsPage;

    /**
    * Specifies the current page number.
    * @var integer
    */
    public $pageNumber;

    /**
    * Parameter to use for the page number
    * @var string
    */
    public $pageParam;

    /**
    * Model column name to display in the list.
    * @var string
    */
    //public $displayColumn;

    /**
    * Model column to use as a record identifier in the details page links
    * @var string
    */
    public $detailsKeyColumn;

    /**
    * Name of the details page URL parameter which takes the record identifier.
    * @var string
    */
    public $detailsUrlParameter;

    public function componentDetails()
    {
        return [
            'name'        => 'fes.birthday::lang.components.list_title',
            'description' => 'fes.birthday::lang.components.list_description'
        ];
    }

    //
    // Properties
    //

    public function defineProperties()
    {
        return [
            'noRecordsMessage' => [
                'title'        => 'fes.birthday::lang.components.list_no_records',
                'description'  => 'fes.birthday::lang.components.list_no_records_description',
                'type'         => 'string',
                'default'      => Lang::get('fes.birthday::lang.components.list_no_records_default'),
                'showExternalParam' => false,
            ],
            'detailsPage' => [
                'title'       => 'fes.birthday::lang.components.list_details_page',
                'description' => 'fes.birthday::lang.components.list_details_page_description',
                'type'        => 'dropdown',
                'showExternalParam' => false,
                'group'       => 'fes.birthday::lang.components.list_detalis_page_link'
            ],
            'detailsUrlParameter' => [
                'title'       => 'fes.birthday::lang.components.list_details_url_parameter',
                'description' => 'fes.birthday::lang.components.list_details_url_parameter_description',
                'type'        => 'string',
                'default'     => 'id',
                'showExternalParam' => false,
                'group'       => 'fes.birthday::lang.components.list_detalis_page_link'
            ],
            'recordsPerPage' => [
                'title'             => 'fes.birthday::lang.components.list_records_per_page',
                'description'       => 'fes.birthday::lang.components.list_records_per_page_description',
                'type'              => 'string',
                'validationPattern' => '^[0-9]*$',
                'validationMessage' => 'fes.birthday::lang.components.list_records_per_page_validation',
                'group'             => 'fes.birthday::lang.components.list_pagination'
            ],
            'pageNumber' => [
                'title'       => 'fes.birthday::lang.components.list_page_number',
                'description' => 'fes.birthday::lang.components.list_page_number_description',
                'type'        => 'string',
                'default'     => '{{ :id }}',
                'group'       => 'fes.birthday::lang.components.list_pagination'
            ],
            'sortColumn' => [
                'title'       => 'fes.birthday::lang.components.list_sort_column',
                'description' => 'fes.birthday::lang.components.list_sort_column_description',
                'type'        => 'autocomplete',
                'group'       => 'fes.birthday::lang.components.list_sorting',
                'showExternalParam' => false
            ],
            'sortDirection' => [
                'title'       => 'fes.birthday::lang.components.list_sort_direction',
                'type'        => 'dropdown',
                'showExternalParam' => false,
                'group'       => 'fes.birthday::lang.components.list_sorting',
                'options'     => [
                    'asc'     => 'fes.birthday::lang.components.list_order_direction_asc',
                    'desc'    => 'fes.birthday::lang.components.list_order_direction_desc'
                ]
            ]
        ];
    }

    public function getDetailsPageOptions()
    {
        $pages = Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');

        $pages = [
            '-' => Lang::get('fes.birthday::lang.components.list_details_page_no')
            ] + $pages;

            return $pages;
    }

    public function getSortColumnOptions()
    {
        $columnNames = ['first_name', 'last_name', 'birth_date'];
        $result = [];

        foreach ($columnNames as $columnName) {
            $result[$columnName] = $columnName;
        }

        return $result;

    }

    //
    // Rendering and processing
    //

    public function onRun()
    {
        $this->prepareVars();
        $this->records = $this->page['records'] = $this->listRecords();
    }

    protected function prepareVars()
    {
        $this->noRecordsMessage = $this->page['noRecordsMessage'] = Lang::get($this->property('noRecordsMessage'));
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->detailsKeyColumn = 'id';
        $this->detailsUrlParameter = $this->page['detailsUrlParameter'] = $this->property('detailsUrlParameter');

        $detailsPage = $this->property('detailsPage');

        if ($detailsPage == '-') {
            $detailsPage = null;
        }

        $this->detailsPage = $this->page['detailsPage'] = $detailsPage;

        if (strlen($this->detailsPage)) {
            if (!strlen($this->detailsKeyColumn)) {
                throw new SystemException(
                    'The details key column should be set to generate links to the details page.'
                );
            }

            if (!strlen($this->detailsUrlParameter)) {
                throw new SystemException(
                    'The details page URL parameter name should be set to generate links to the details page.'
                );
            }
        }
    }

    protected function listRecords()
    {
        $modelClassName = 'Fes\Birthday\Models\User';
        $model = new $modelClassName();
        $model = $this->sort($model);
        $records = $this->paginate($model);
        return $records;
    }

    protected function paginate($model)
    {
        $recordsPerPage = trim($this->property('recordsPerPage'));

        if (!strlen($recordsPerPage)) {
            return $model->get();
        }

        if (!preg_match('/^[0-9]+$/', $recordsPerPage)) {
            throw new SystemException('Invalid records per page value.');
        }

        $pageNumber = trim($this->property('pageNumber'));

        if (!strlen($pageNumber) || !preg_match('/^[0-9]+$/', $pageNumber)) {
            $pageNumber = 1;
        }

        return $model->paginate($recordsPerPage, $pageNumber);
    }

    protected function sort($model)
    {

        $sortColumn = trim($this->property('sortColumn'));

        if (!strlen($sortColumn)) {
            return;
        }

        $sortDirection = trim($this->property('sortDirection'));

        if ($sortDirection !== 'desc') {
            $sortDirection = 'asc';
        }

        return $model->orderBy($sortColumn, $sortDirection);
    }
}
