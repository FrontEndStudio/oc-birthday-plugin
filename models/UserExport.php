<?php namespace Fes\Birthday\Models;

use Backend\Models\ExportModel;
use ApplicationException;

class UserExport extends ExportModel
{
    public $table = 'fes_birthday_user';
    
    public function exportData($columns, $sessionKey = null)
    {
        $result = self::make()->get()->toArray();
        return $result;
    }
}
