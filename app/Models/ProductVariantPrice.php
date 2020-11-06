<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    function variant1()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one', 'id');
    }
    function variant2()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two', 'id');
    }
    function variant3()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three', 'id');
    }

}
