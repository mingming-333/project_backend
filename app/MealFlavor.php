<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MealFlavor extends Model
{
    use Notifiable;
    
    protected $table = 'mealflavor';
    protected $primaryKey = "MealFlavorID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'MealID',
        'FlavorTypeID'
    ];
}
