<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    //
    use SoftDeletes;
    
    protected $table = 'productos';
    
    protected $primaryKey = 'id';

    

    public static function getProduct($reference)
    {
        $name = str_replace(' ', '', $reference);
        $name = str_replace('"', '', $name);

        $product = Products::where('reference', 'LIKE', '%'.$name.'%')
                ->first();

                if (!is_object($product)) {
                return false;
                }
        return $product;

    }
}
