<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductNotaCredito extends Model
{
    protected $table = "product_nota_credito";


    protected $fillable = [
        'id',
        'product_id',
        'nota_credito_id',
        'qty',
        'net_unit_price',
        'discount',
        'tax_rate',
        'tax',
        'total',
        'created_at',
        'updated_at',
        'ice',
        'irbpnr'
    ];
}
