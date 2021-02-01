<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OrderItemFlavor extends Model
{
    use Notifiable;
    
    protected $table = 'orderitemflavor';
    protected $primaryKey = "OrderItemFlavorID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'OrderItemID',
        'FlavorID',
    ];
}
