<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Biller extends Model
{
    protected $fillable =[
        'id',
        'name',
        'created_at',
        'updated_at',
        'company_id',
        'secuencial_factura',
        'secuencial_guia',
        'secuencial_nota_credito',
        'secuencial_nota_debito',
        'secuencial_liquidacion',
        'secuencial_retencion',
        'codigo',
        'warehouse_id',
        'is_active',
        'asignado',
        'user_id'
    ];
}
