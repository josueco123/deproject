<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    //
    protected $table = 'municipios';

    public static function getCity($name,$department_id)
    {
        $city = Cities::where('nombre', 'LIKE', '%'.$name.'%')
            ->where('departamento_id', '=', $department_id)
            ->first();
        return $city;
    }
}
