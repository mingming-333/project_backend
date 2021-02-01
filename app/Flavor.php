<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Flavor extends Model
{
    use Notifiable;
    
    protected $table = 'flavor';
    protected $primaryKey = "FlavorID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'FlavorName','ExtraPrice','FlavorTypeID'
    ];
}
