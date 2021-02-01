<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MenuType extends Model
{
    use Notifiable;
    
    protected $table = 'menutype';
    protected $primaryKey = "MenuTypeID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'MenuTypeName','StoreID'
    ];
}
