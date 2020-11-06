<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    function productvarientprices()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id', 'id');
    }

    function productvarients()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }


}
