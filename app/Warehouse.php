<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable =[

        "name", "phone", "email", "address", "is_active","company_id","codigo","emisor_id","codigo_numerico","cod_ref"
    ];
}
