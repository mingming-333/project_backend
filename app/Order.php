<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Order extends Model
{
    use Notifiable;
    
    protected $table = 'orders';
    protected $primaryKey = "OrderID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Price',
        'Status',
        'Memo',
        'StoreID',
        'CustomerID',
        'DateTime',
        'UpdateTime',
        'EstimatedTime',
        'changed',
        'OrderNumber',
        'IsTakeOut',
        'ServiceFee',
        'Destination',
        'TotalAmount'
    ];

    public function orderItems()
    {
        return $this->hasMany('App\OrderItem', 'OrderID', 'OrderID');
    }
}
