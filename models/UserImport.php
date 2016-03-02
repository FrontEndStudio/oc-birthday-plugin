<?php namespace Fes\Birthday\Models;

use Backend\Models\ImportModel;
use ApplicationException;

class UserImport extends ImportModel
{

    public $table = 'fes_birthday_user';

    public $rules = [
        'first_name' => 'required'
    ];

    public function importData($results, $sessionKey = null)
    {
        $firstRow = reset($results);

        foreach ($results as $row => $data) {

            try {

                if (!$first_name = array_get($data, 'first_name')) {
                    $this->logSkipped($row, 'Missing post first_name');
                    continue;
                }

                // find or create
                $user = User::make();

                if ($this->update_existing) {
                    $user = $this->findDuplicateUser($data) ?: $user;
                }

                $userExists = $user->exists;

                // set attributes
                $except = ['id'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    $user->{$attribute} = $value ?: null;
                }

                $user->forceSave();

                if ($userExists) {
                    $this->logUpdated();
                } else {
                    $this->logCreated();
                }
            } catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    protected function findDuplicateUser($data)
    {

        if ($id = array_get($data, 'id')) {
            return User::find($id);
        }

        $first_name = array_get($data, 'first_name');
        $last_name = array_get($data, 'last_name');
        $user = User::where('first_name', $first_name, 'last_name', $last_name);

        return $user->first();
    }
}
