<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable =[

        "id","name", "parent_id", "is_active","company_id","as_menu","web","mobile","created_at","updated_at"
    ];

    public function product()
    {
    	return $this->hasMany('App/Product');

    }
}
