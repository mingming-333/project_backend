<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreBusinessHour extends Model
{
    protected $table = 'storebusinesshour';
    protected $primaryKey = "id";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'StoreID', 'BusinessHour', 'StoreState'
    ];
}
