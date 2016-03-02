<?php namespace Fes\Birthday\Controllers;

use Flash;
use Backend\Classes\Controller;
use BackendMenu;
use Fes\Birthday\Models\User;

class Users extends Controller
{
    public $implement = [
      'Backend\Behaviors\ListController',
      'Backend\Behaviors\FormController',
      'Backend\Behaviors\ReorderController',
      'Backend\Behaviors\ImportExportController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Fes.Birthday', 'main-menu-item');
    }

    public function index_onDelete()
    {

        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $userId) {

                if (!$user = User::find($userId)) {
                    continue;
                }

                $user->delete();
            }

            Flash::success('Successfully deleted those user(s).');

        } else {
            Flash::error('Nothing deleted');
        }

        return $this->listRefresh();
    }
}
