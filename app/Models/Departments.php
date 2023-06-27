<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    //
    protected $table = 'departamentos';

    public static function getDepartmentCode($name)
    {
        $department = Departments::where('nombre', 'LIKE', '%'.$name.'%')
                                    ->orderBy('id', 'desc')
                                    ->first();
        return $department->codigo;
    }
}
