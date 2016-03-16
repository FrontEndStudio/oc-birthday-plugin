<?php namespace Fes\Birthday\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Lang;
use Exception;
use SystemException;
use Db;

class AgeList extends ComponentBase
{

    public $records;
    public $noRecordsMessage;
    public $detailsPage;
    public $pageNumber;
    public $daysPast;
    public $daysFuture;
    public $sortColumn;
    public $sortDirection;
    public $pageParam;
    public $detailsKeyColumn;
    public $detailsUrlParameter;

    public function componentDetails()
    {
        return [
            'name'        => 'fes.birthday::lang.components.age_title',
            'description' => 'fes.birthday::lang.components.age_description'
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
                'group'       => 'fes.birthday::lang.components.list_details_page_link'
            ],
            'detailsUrlParameter' => [
                'title'       => 'fes.birthday::lang.components.list_details_url_parameter',
                'description' => 'fes.birthday::lang.components.list_details_url_parameter_description',
                'type'        => 'string',
                'default'     => 'id',
                'showExternalParam' => false,
                'group'       => 'fes.birthday::lang.components.list_details_page_link'
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
            'daysPast' => [
                'title'             => 'fes.birthday::lang.components.agelist_records_days_past',
                'description'       => 'fes.birthday::lang.components.agelist_records_days_past_description',
                'type'              => 'string',
                'validationPattern' => '^[0-9]*$',
                'validationMessage' => 'fes.birthday::lang.components.agelist_records_days_past_validation',
                'group'             => 'fes.birthday::lang.components.agelist_days'
            ],
            'daysFuture' => [
                'title'             => 'fes.birthday::lang.components.agelist_records_days_future',
                'description'       => 'fes.birthday::lang.components.agelist_records_days_future_description',
                'type'              => 'string',
                'validationPattern' => '^[0-9]*$',
                'validationMessage' => 'fes.birthday::lang.components.agelist_records_days_future_validation',
                'group'             => 'fes.birthday::lang.components.agelist_days'
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
        $this->daysPast = $this->page['daysPast'];
        $this->daysFuture = $this->page['daysFuture'];

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

        $daysPast = trim($this->property('daysPast'));

        if (!strlen($daysPast) || !preg_match('/^[0-9]+$/', $daysPast)) {
            $daysPast = 1;
        }

        $daysFuture = trim($this->property('daysFuture'));

        if (!strlen($daysFuture) || !preg_match('/^[0-9]+$/', $daysFuture)) {
            $daysFuture = 1;
        }

        $sortColumn = trim($this->property('sortColumn'));

        if (!strlen($sortColumn)) {
            $sortColumn = 'upcoming_days';
        }

        $sortDirection = trim($this->property('sortDirection'));

        if ($sortDirection !== 'desc') {
            $sortDirection = 'asc';
        }

        $records = Db::table('fes_birthday_user')
            ->select(Db::raw("*,
                CONCAT_WS(' ', NULLIF(first_name, ''), NULLIF(middle_name, ''), NULLIF(last_name, '')) AS full_name,
                DATE_FORMAT(birth_date, '%d') AS date_dd,
                DATE_FORMAT(birth_date, '%m') AS date_mm,
                DATE_FORMAT(birth_date, '%Y') AS date_yyyy,
                DATE_FORMAT(birth_date, '%M') AS date_month_name,
                DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth_date, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth_date, '00-%m-%d')) AS age,
                FLOOR((UNIX_TIMESTAMP(CONCAT(((SUBSTR(birth_date,-14, 5)  < RIGHT(CURRENT_DATE, 5)) + YEAR(CURRENT_DATE)), SUBSTRING(birth_date, -15,6))) - UNIX_TIMESTAMP(CURRENT_DATE)) / 86400) AS upcoming_days"))
            ->whereRaw("MAKEDATE( YEAR(CURDATE()), DAYOFYEAR(birth_date) )
                BETWEEN DATE_ADD( CURDATE(), INTERVAL -".$daysPast."  DAY ) AND DATE_ADD( CURDATE(), INTERVAL ".$daysFuture."  DAY )
                OR MAKEDATE( YEAR(CURDATE())+1, DAYOFYEAR(birth_date) )
                BETWEEN DATE_ADD( CURDATE(), INTERVAL -".$daysPast."  DAY ) AND DATE_ADD( CURDATE(), INTERVAL ".$daysFuture."  DAY ) AND status=1")
            ->orderBy($sortColumn, $sortDirection)
            ->get();

        return $records;

    }

}
