<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetalleGuia extends Model
{
    protected $table="detalles_guia";
    protected $fillable=[
        'id',
        'product_id',
        'cantidad',
        'created_at',
        'updated_at',
        'guia_id',
    ];
}
