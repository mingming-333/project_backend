<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FlavorType extends Model
{
    use Notifiable;
    
    protected $table = 'flavortype';
    protected $primaryKey = "FlavorTypeID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'FlavorTypeName','StoreID','isRequired', 'isMultiple'
    ];
}
