<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable =[

        "reference_no", "user_id", "warehouse_id", "supplier_id", "item", "total_qty", 
        "total_discount", "total_tax", "total_cost", "order_tax_rate", 
        "order_tax", "order_discount", "shipping_cost",
         "grand_total","paid_amount", "status", "payment_status",
         "document", "note","company_id","clave_acceso","estado_sri","fecha_emision","fecha_autorizacion",
         "secuencial","plazo","forma_pago","tipo_documento","liquidacion","ambiente","numero_documento"
    ];
}
