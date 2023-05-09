<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaDebito extends Model
{
    protected $table = "nota_debito";


    protected $fillable = [
        'id',
        'customer_id',
        'warehouse_id',
        'user_id',
        'tipo_documento',
        'fecha_emision',
        'fecha_autorizacion',
        'numero_comprobante',
        'motivo',
        'subtotal_iva12',
        'subtotal_iva0',
        'subtotal_noiva',
        'subtotal_extiva',
        'total_discount',
        'total_price',
        'total_ice',
        'grand_total',
        'updated_at',
        'total_irbpnr',
        'created_at',
        'estado_sri',
        'clave_acceso',
        'fecha_emision_documento',
        'numero_nota',
        'auxiliar_secuencial',
        'tipo_identificacion',
        'ambiente'
    ];
}
