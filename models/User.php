<?php namespace Fes\Birthday\Models;

use Model;

class User extends Model
{

    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;

    public $rules = [
    ];

    public $timestamps = false;

    public $table = 'fes_birthday_user';

    //
    // Scopes
    //

    public function scopeIsActiveStatus($query)
    {
        return $query
        ->whereNotNull('status')
        ->where('status', true);
    }
}
