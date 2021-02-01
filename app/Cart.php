<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cart extends Model
{
    use Notifiable;
    
    protected $table = 'cart';
    protected $primaryKey = "CartID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'TypeName',
        'Quantity',
        'Amount',
        'FoodCourt',
        'StoreName',
        'DateTime',
        'MealID',
        'UserID',
        'StoreID',
        'Memo'
    ];
}
