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
        if (!is_object($department)) {
                return false;
                }
        return $department->codigo;
    }

    public static function getDepartmentByCityCode($code)
    {
        $department = Departments::where('codigo',$code)->first();
        if (!is_object($department)) {
            return false;
            }
        return $department->codigo;
    }
}
