<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Store extends Model
{
    use Notifiable;

    protected $table = 'store';
    protected $primaryKey = "StoreID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'StoreName', 'StoreTheme', 'StoreDescription',
        'FoodCourtID', 'SuperUserID', 'StoreImagePath',
        'Offset', 'IsOpen'
    ];
}
