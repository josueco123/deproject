<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    //

    protected $table = 'productos';
    
    protected $primaryKey = 'id';

    public static function getCodeProduct($reference)
    {
        $name = str_replace(' ', '', $reference);
        $name = str_replace('"', '', $name);

        $product = Products::where('reference', 'LIKE', '%'.$name.'%')
                ->first();

                if (!is_object($product)) {
                return false;
                }
        return $product->code;

    }
}
