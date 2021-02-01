<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreBusinessDay extends Model
{
    protected $table = 'storebusinessday';
    protected $primaryKey = "id";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'StoreID', 'BusinessDay', 'StoreState'
    ];
}
