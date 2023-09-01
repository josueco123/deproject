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
        if (!is_object($city)) {
                return false;
                }
        return $city;
    }

    public static function getCityByName($name)
    {
        $cityName = $name == 'El Dificil' ? 'Ariguani' : $name;
        
        if(str_contains($cityName,"(Q)"))
        $cityName = str_replace(" (Q)", "", $cityName);

        $city = Cities::where('nombre', 'LIKE', '%'.$cityName.'%')
            ->first();
            if (!is_object($city)) {
                return false;
                }
        return $city;
    }
}
