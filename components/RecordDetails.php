<?php namespace Fes\Birthday\Components;

use Lang;
use Cms\Classes\ComponentBase;
use SystemException;

class RecordDetails extends ComponentBase
{
    /**
    * A model instance to display
    * @var \October\Rain\Database\Model
    */
    public $record = null;

    /**
    * Message to display if the record is not found.
    * @var string
    */
    public $notFoundMessage;

    /**
    * Model column to display on the details page.
    * @var string
    */
    public $displayColumn;

    /**
    * Model column to use as a record identifier for fetching the record from the database.
    * @var string
    */
    public $modelKeyColumn;

    /**
    * Identifier value to load the record from the database.
    * @var string
    */
    public $identifierValue;

    public function componentDetails()
    {
        return [
            'name'        => 'fes.birthday::lang.components.details_title',
            'description' => 'fes.birthday::lang.components.details_description'
        ];
    }

    //
    // Properties
    //

    public function defineProperties()
    {
        return [
            'identifierValue' => [
                'title'       => 'fes.birthday::lang.components.details_identifier_value',
                'description' => 'fes.birthday::lang.components.details_identifier_value_description',
                'type'        => 'string',
                'default'     => '{{ :id }}',
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('fes.birthday::lang.components.details_identifier_value_required')
                    ]
                ]
            ],
            'modelKeyColumn' => [
                'title'       => 'fes.birthday::lang.components.details_key_column',
                'description' => 'fes.birthday::lang.components.details_key_column_description',
                'type'        => 'autocomplete',
                'default'     => 'id',
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('fes.birthday::lang.components.details_key_column_required')
                    ]
                ],
                'showExternalParam' => false
            ],
            'notFoundMessage' => [
                'title'       => 'fes.birthday::lang.components.details_not_found_message',
                'description' => 'fes.birthday::lang.components.details_not_found_message_description',
                'default'     => Lang::get('fes.birthday::lang.components.details_not_found_message_default'),
                'type'        => 'string',
                'showExternalParam' => false
            ]
        ];
    }

    //
    // Rendering and processing
    //

    public function onRun()
    {
        $this->prepareVars();
        $this->record = $this->page['record'] = $this->loadRecord();
    }

    protected function prepareVars()
    {
        $this->notFoundMessage = $this->page['notFoundMessage'] = Lang::get($this->property('notFoundMessage'));
        $this->modelKeyColumn = $this->page['modelKeyColumn'] = $this->property('modelKeyColumn');
        $this->identifierValue = $this->page['identifierValue'] = $this->property('identifierValue');

        if (!strlen($this->modelKeyColumn)) {
            throw new SystemException('The model key column name is not set.');
        }

    }

    protected function loadRecord()
    {
        if (!strlen($this->identifierValue)) {
            return;
        }

        $modelClassName = 'Fes\Birthday\Models\User';
        $model = new $modelClassName();
        return $model->where($this->modelKeyColumn, '=', $this->identifierValue)->first();
    }
}
