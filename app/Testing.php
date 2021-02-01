<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Testing extends Model
{
    protected $table = 'testing';
    protected $primaryKey = "id"; //基本上是做查詢用，不會有人跟他重複
    public $timestamps = false;

    protected $fillable = [
        'name', 'number'
    ];
    //
}
