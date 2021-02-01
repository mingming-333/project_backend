<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use App\Store;
use App\User;
use App\Meal;
use App\Flavor;
use App\MenuType;
use App\OrderItem;
use App\OrderItemFlavor;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) 
{
    $date =  $faker->dateTime($max = 'now', $timezone = 'Asia/Taipei');

    return [
        'Price' => 0,
        'Status' => $faker->randomElements($array = array(0, 1, 2, 3, 99), $count = 1)[0],
        'Memo' => $faker->realText($maxNbChars = 30),
        'StoreID' => Store::all()->random()->StoreID,
        'CustomerID' => User::all()->random()->id,
        'DateTime' => $date,
        'UpdateTime' => $date
    ];
});

$factory->define(OrderItem::class, function (Faker $faker, $params) 
{
    $storeID = Order::find($params['OrderID'])->StoreID;

    $meal = MenuType::where('StoreID', $storeID)
                    ->join('meal', 'meal.MenuTypeID', '=', 'menutype.MenuTypeID')
                    ->get()
                    ->random();
    
    $quantity = $faker->numberBetween($min = 1, $max = 5);

    return [
        'Quantity' => $quantity,
        'Amount' => $meal->MealPrice * $quantity,
        'MealID' => $meal->MealID
    ];
});

$factory->define(OrderItemFlavor::class, function (Faker $faker, $params)
 {   
    // $mealID = OrderItem::find($params['OrderItemID'])->MealID;

    // $flavor = Meal::where('meal.MealID', $mealID)
    //             ->join('mealflavor', 'mealflavor.MealID', '=', 'meal.MealID')
    //             ->join('flavor', 'flavor.FlavorTypeID', '=', 'mealflavor.FlavorTypeID')
    //             ->get()
    //             ->random();

    return [
    ];
});
