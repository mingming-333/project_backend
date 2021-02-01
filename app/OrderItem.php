<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OrderItem extends Model
{
    use Notifiable;
    
    protected $table = 'orderitem';
    protected $primaryKey = "OrderItemID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'OrderItemID',
        'TypeName',
        'Quantity',
        'Amount',
        'FoodCourt',
        'StoreName',
        'DateTime',
        'OrderID',
        'MealID',
        'Memo'
    ];

    public function orderItemFlavors()
    {
        return $this->hasMany('App\Flavor', 'FlavorID', 'FlavorID');
    }
}
