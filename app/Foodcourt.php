<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foodcourt extends Model
{
    //
    protected $table = 'foodcourt';
    public $timestamps = false;
    protected $primaryKey ="FoodCourtID";
    protected $fillable = [
        'FoodCourtName','FoodCourtDescription','SuperUserID'
    ];

}
