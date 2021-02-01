<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Meal extends Model
{
    use Notifiable;

    protected $table = 'meal';
    protected $primaryKey = "MealID";
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'MealName',
        'MealPrice',
        'MealToday',
        'MealSoldOut',
        'MealImagePath',
        'MealCalorie',
        'MealDescription',
        'MenuTypeID',
        'del_flag'
    ];
}
