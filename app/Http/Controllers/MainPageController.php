<?php

namespace App\Http\Controllers;

use App\Store;
use App\Meal;
use App\FoodCourt;
use App\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class MainPageController extends Controller
{
    public function getMainPageData()
    {
        $data = collect([
            $this->choosingGame(),
            $this->carouselAnimation(),
            $this->special_restaurant(),
            $this->eat_forever(),
            $this->meal_ranking(),
            $this->restaurant_cart_view(),
            //$this->announcement(),
        ]);

        return collect([
            'componentArr' => $data
        ]);
    }

    public function special_restaurant()  //type 0
    {
        $stores = Store::inRandomOrder()->get();

        $data = collect();

        foreach($stores as $store)
        {
            $data->push(collect([
                'id' => $store->StoreID,
                'isStoreOpen' => $store->IsOpen,
                'img' => $store->StoreImagePath,
                'name' => $store->StoreName
            ]));
        }

        return collect([
            'type' => 0,
            'title' => '精選餐廳',
            'data' => $data
        ]);
    }

    public function eat_forever()  //type 1
    {
        $meals = Meal::inRandomOrder()
                    ->limit(6)
                    ->join('menutype', 'menutype.MenuTypeID', '=', 'meal.MenuTypeID')
                    ->where('MealSoldOut', false)
                    ->where('del_flag', false)
                    ->get();

        $data = collect();

        foreach($meals as $meal)
        {
            $data->push(collect([
                'storeID' => $meal->StoreID,
                'mealID' => $meal->MealID,
                'img' => $meal->MealImagePath,
                'name' => $meal->MealName
            ]));
        }

        return collect([
            'type' => 1,
            'title' => '永遠吃不膩',
            'data' => $data
        ]);
    }

    public function restaurant_cart_view() //type 2
    {
        $stores = Store::join('foodcourt', 'foodcourt.FoodCourtID', '=', 'store.FoodCourtID')
                        ->get();

        $data = collect();

        foreach($stores as $store)
        {
            $data->push(collect([
                'img' => $store->StoreImagePath,
                'subtitle' => $store->FoodCourtName,
                'storeName' => $store->StoreName,
                'storeID' => $store->StoreID,
                'isStoreOpen' => $store->IsOpen,
                'memo' => $store->StoreDescription
            ]));
        }

        return collect([
            'type' => 2,
            'data' => $data
        ]);
    }

    public function meal_ranking() //type 3
    {
        $mealRanking = Cache::remember('MealRanking', 1800, function () 
        {
            return OrderItem::join('orders', 'orderItem.OrderID', '=', 'orders.OrderID')
                                ->join('store', 'orders.StoreID', '=', 'store.StoreID')
                                ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
                                ->where('del_flag', false)
                                ->where('Status', 3)
                                ->where('orders.DateTime', '>', Carbon::now()->subDays(7)->toDateTimeString())
                                ->selectRaw('orders.StoreID as StoreID, meal.MealID as MealID, MealImagePath, MealName, store.StoreName as StoreName, MealDescription, SUM(Quantity) AS count')
                                ->groupBy('meal.MealID')
                                ->orderBy('count', 'desc')
                                ->take(5)
                                ->get();
        });
        

        $data = collect();

        foreach($mealRanking as $meal)
        {
            $data->push(collect([
                'storeID' => $meal->StoreID,
                'mealID' => $meal->MealID,
                'img' => $meal->MealImagePath,
                'mealName' => $meal->MealName,
                'storeName' => $meal->StoreName,
                'description' => $meal->MealDescription
            ]));
        }

        return collect([
            'type' => 3,
            'title' => '餐點人氣排行',
            'data' => $data
        ]);
    }

    public function carouselAnimation() //type 4 
    {
        $data = collect();
        // $meals = Meal::inRandomOrder()
        //             ->limit(3)
        //             ->where('MealSoldOut', false)
        //             ->join('menutype', 'menutype.MenuTypeID', '=', 'meal.MenuTypeID')
        //             ->get();

        // foreach($meals as $meal)
        // {
        //     $data->push(collect([
        //         'act' => 'scene',
        //         'img'=> 'https://cchen4.csie.ntust.edu.tw/storage/' . $meal->MealImagePath,
        //         'sceneName' => 'mealDetail',
        //         'prop' => collect([
        //             'storeID' => $meal->StoreID,
        //             'mealID' => $meal->MealID
        //         ])
        //     ]));
        // }

        $data->push(collect([
                'act' => 'scene',
                'img'=> 'https://cchen4.csie.ntust.edu.tw/storage/hungryNTUST/豪享來.png',
                'sceneName' => 'restaurantPage',
                'prop' => collect([
                    'storeID' => 1,
                ])
        ]));

        $data->push(collect([
                'act' => 'scene',
                'img'=> 'https://cchen4.csie.ntust.edu.tw/storage/hungryNTUST/赤糰.png',
                'sceneName' => 'restaurantPage',
                'prop' => collect([
                    'storeID' => 2,
                ])
        ]));

        $data->push(collect([
                'act' => 'scene',
                'img'=> 'https://cchen4.csie.ntust.edu.tw/storage/hungryNTUST/穀倉.png',
                'sceneName' => 'restaurantPage',
                'prop' => collect([
                'storeID' => 3,
                ])
        ]));

        $data->push(collect([
            'act' => 'link',
            'img'=> 'https://cchen4.csie.ntust.edu.tw/storage/hungryNTUST/fb.png',
            'url' => 'https://www.facebook.com/%E9%BB%91%E7%99%BD%E9%BB%9E-101344165182005'
        ]));

        $data->push(collect([
            'act' => 'img',
            'img'=> 'https://cchen4.csie.ntust.edu.tw/storage/hungryNTUST/驗證信提醒.png',
        ]));

        // $data->push(collect([
        //     'act' => 'scene',
        //     'img'=> 'https://i.imgur.com/gSwhoIJ.jpg',
        //     'sceneName' => 'mealDetail',
        //     'prop' => collect([
        //         'storeID' => 1,
        //         'mealID' => 1
        //     ])
        // ]));

        return collect([
            'type' => 4,
            'data' => $data
        ]);
    }

    public function announcement() //type 5
    {
        $data = collect([
            'title' => '公告',
            'msg' => '即將於今晚 0:00~2:00 進行停機維修...etc.'
        ]);

        return collect([
            'type' => 4,
            'data' => $data
        ]);
    }

    public function choosingGame() //type 6
    {
        return collect([
            'type' => 6,
            'isMealShow' => true,
            'isStoreShow' => true
        ]);
    }

    public function choosingGameForStore() 
    {
        $store = Store::inRandomOrder()
                        ->join('foodcourt', 'foodcourt.FoodCourtID', '=', 'store.FoodCourtID')
                        ->first();
        
        return collect([
            'img' => $store->StoreImagePath,
            'subtitle' => $store->FoodCourtName,
            'storeName' => $store->StoreName,
            'storeID' => $store->StoreID,
            'memo' => $store->StoreDescription
        ]);
    }

    public function choosingGameForMeal() 
    {
        $meal = Meal::inRandomOrder()
                    ->join('menutype', 'menutype.MenuTypeID', '=', 'meal.MenuTypeID')
                    ->join('store', 'menutype.StoreID', '=', 'store.StoreID')
                    ->whereIn('menutype.MenuTypeID', [1,2,3,4,5,6,7,8,14,15])
                    ->where('del_flag', false)
                    ->where('MealSoldOut', false)
                    ->first();
        
        return collect([
            'storeID' => $meal->StoreID,
            'storeName' => $meal->StoreName,
            'mealID' => $meal->MealID,
            'img' => $meal->MealImagePath,
            'mealName' => $meal->MealName,
            'memo' => $meal->MealDescription
        ]);
    }
}
