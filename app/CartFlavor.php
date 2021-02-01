<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartFlavor extends Model
{
    protected $table = "cartFlavor";
    protected $primaryKey = "CartFlavorID";
    public $timestamps = false;

    protected $fillable = [
        'CartID',
        'FlavorTypeID',
        'FlavorID'
    ];
}
