<?php

namespace App\Http\Controllers;

use App\MenuType;
use App\Meal;
use App\Store;
use Storage;
use App\MealFlavor;
use App\FlavorType;
use App\Flavor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MealController extends Controller
{
    private const MEAL_CACHE_KEY_PREFIX = 'store_meals_';

    public function getAllMeals($storeId)
    {
        $collection = collect();

        $meals = $this->getMealCache($storeId);

        if ($meals->isEmpty()) 
        {
            return \response("", 204);
        }

        $menuTypes = $meals->groupBy('MenuTypeID');
        $items = null;

        foreach ($menuTypes as $menuType) {
            $items = collect();

            foreach ($menuType as $meal) {
                $items->push(
                    collect([
                        'id' => $meal->MealID,
                        'name' => $meal->MealName,
                        'price' => $meal->MealPrice,
                        'checked' => $meal->MealSoldOut
                    ])
                );
            }

            $collection->push(
                collect([
                    'id' => $menuType[0]['MenuTypeID'],
                    'type' => $menuType[0]['MenuTypeName'],
                    'items' => $items
                ])
            );
        }

        return $collection;
    }

    public function getStoreMealList($storeId)
    {
        $meals = $this->getMealCache($storeId)
                    ->where('MealSoldOut', false);

        if ($meals->isEmpty()) 
        {
            return \response("", 204);
        }

        $mealsToday = $meals->where('MealToday', 1);
        $mealTodayList = collect();

        foreach ($mealsToday as $mealToday) {
            $mealTodayList->push(
                collect([
                    'mealName' => $mealToday->MealName,
                    'mealID' => $mealToday->MealID,
                    'mealDescription' => $mealToday->MealDescription,
                    'mealPrice' => $mealToday->MealPrice,
                    'img' => $mealToday->MealImagePath
                ])
            );
        }

        $menuTypes = $meals->groupBy('MenuTypeID');
        $menu = collect();
        $mealList = null;

        $menu->push(
            collect([
                'mealType' => '今日特餐',
                'mealList' => $mealTodayList
            ])
        );

        foreach ($menuTypes as $menuType) {
            $mealList = collect();

            foreach ($menuType as $meal) {
                $mealList->push(
                    collect([
                        'mealName' => $meal->MealName,
                        'mealID' => $meal->MealID,
                        'mealDescription' => $meal->MealDescription,
                        'mealPrice' => $meal->MealPrice,
                        'img' => $meal->MealImagePath
                    ])
                );
            }

            if ($mealList->isNotEmpty()) {
                $menuTemp = collect([
                    'mealType' => $meal->MenuTypeName,
                    'mealList' => $mealList
                ]);

                $menu->push($menuTemp);
            }
        }

        return collect([
            'storeID' => $meals[0]['StoreID'],
            'storeName' => $meals[0]['StoreName'],
            'isStoreOpen' => $meals[0]['IsOpen'],
            'menu' => $menu
        ]);
    }

    public function getTodaySpecial()
    {
        $allStoreID = $this->getAllStoreID();

        $mealsToday = collect();

        foreach($allStoreID as $storeID)
        {
            $mealsToday = $mealsToday->merge($this->getMealCache($storeID));
        }
  
        if ($mealsToday->isEmpty()) 
        {
            return \response("", 204);
        }

        $mealsTodayByStore = $mealsToday->where('MealToday', true)->groupBy('StoreID');
        $stores = collect();
        $mealList = null;

        foreach ($mealsTodayByStore as $store) {
            $mealList = collect();

            foreach ($store as $meals) {
                $mealList->push(
                    collect([
                        'mealName' => $meals->MealName,
                        'mealID' => $meals->MealID,
                        'mealDescription' => $meals->MealDescription,
                        'mealPrice' => $meals->MealPrice,
                        'img' => $meals->MealImagePath
                    ])
                );
            }

            $stores->push(
                collect([
                    'storeID' => $store[0]['StoreID'],
                    'storeName' => $store[0]['StoreName'],
                    'mealList' => $mealList
                ])
            );
        }

        return collect([
            'title' => '今日特餐',
            'store' => $stores
        ]);
    }

    public function getMeal($storeId, $mealId)
    {
        $meal = $this->getMealCache($storeId)
                    ->where("MealID", $mealId)
                    ->first();

        if ($meal == null) {
             return \response("", 204);
        }

        $collection = collect([
            'storeName' => $meal->StoreName,
            'foodDescription' => $meal->MealDescription,
            'todaySpecial' => $meal->MealToday,
            'foodName' =>  $meal->MealName,
            'foodPrice' => $meal->MealPrice,
            'foodIsSoldOut' => $meal->MealSoldOut,
            'foodTypeID' => $meal->MenuTypeID,
            'foodType' => $meal->MenuTypeName,
            'calories' => $meal->MealCalorie,
            'imageUri' => $meal->MealImagePath,
            'isStoreOpen' => $meal->IsOpen
        ]);

        return $collection;
    }

    public function storeMeal(Request $request, $storeId)
    {
        $data = $request->all();
        $data['storeID'] = $storeId;
        
        $validator = Validator::make($data, [
            'storeID' => 'required|exists:store',
            'foodDescription' => 'nullable|string',
            'todaySpecial' => 'boolean',
            'foodPrice' => 'required|integer|min:0',
            'foodIsSoldOut' => 'boolean',
            'foodTypeID' => 'exists:menuType,MenuTypeID',
            'calories' => 'integer|min:0',
            'flavors.*.name' => 'required|string',
            'flavors.*.isRequired' => 'boolean',
            'flavors.*.isMultiple' => 'boolean',
            'flavors.*.items.*.flavorName' => 'required|string',
            'flavors.*.items.*.extraPrice' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return \response($validator->errors(), 422);
        }

        $new_file_name = "";

        if ($request->has('imageFile')) {
            $image = $request->imageFile;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $new_file_name = date('YmdHis') . '_' . \str_random(5) . '.' . 'png';

            Storage::put('public/' . $new_file_name, base64_decode($image));
        }

        $meal = Meal::create([
            'MealDescription' => $request->input('foodDescription') ?? "",
            'MealToday' => $request->input('todaySpecial'),
            'MealName' => $request->input('foodName'),
            'MealPrice' => $request->input('foodPrice'),
            'MealSoldOut' => $request->input('foodIsSoldOut'),
            'MenuTypeID' => $request->input('foodTypeID'),
            'MealCalorie' => $request->input('calories') ?? 0,
            'MealImagePath' => $new_file_name
        ]);

        if ($request->has('flavors')) {
            $flavorTypes = $request->input('flavors');
            $items = null;
            $flavors = null;

            foreach ($flavorTypes as $ft) 
            {
                $flavorType = FlavorType::create([
                    'FlavorTypeName' => $ft['name'] ?? '',
                    'StoreID' => $storeId,
                    'isRequired' => $ft['isRequired'] ?? false,
                    'isMultiple' => $ft['isMultiple'] ?? false
                ]);

                MealFlavor::create([
                    'MealID' => $meal->MealID,
                    "FlavorTypeID" => $flavorType->FlavorTypeID
                ]);

                
                if (array_key_exists('items', $ft)) 
                {
                    $items = $ft['items'];
                    $flavors = array();

                    foreach ($items as $item) {
                        $flavors[] = [
                            'FlavorName' => $item['flavorName'] ?? '',
                            'ExtraPrice' => $item['extraPrice'] ?? 0,
                            'FlavorTypeID' => $flavorType->FlavorTypeID,
                        ];
                    }

                    Flavor::insert($flavors);
                }
            }
        }

        $this->updateMealCache($storeId);
    }

    public function updateMeal(Request $request, $storeId, $mealId)
    {
        $data = $request->all();
        $data['storeID'] = $storeId;
        $data['mealID'] = $mealId;

        $validator = Validator::make($data, [
            'storeID' => 'required|exists:store',
            'mealID' => 'required|exists:meal',
            'foodDescription' => 'nullable|string',
            'todaySpecial' => 'boolean',
            'foodName' => 'required|string',
            'foodPrice' => 'required|integer|min:0',
            'foodIsSoldOut' => 'boolean',
            'foodTypeID' => 'required|exists:menuType,MenuTypeID',
            'calories' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return \response($validator->errors(), 422);
        }

        $new_file_name = $this->getMealCache($storeId)
                            ->where("MealID", $mealId)
                            ->first()
                            ->MealImagePath;

        if ($request->has('imageFile')) {
            $image = $request->imageFile;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $new_file_name = date('YmdHis') . '_' . \str_random(5) . '.' . 'png';

            Storage::put('public/' . $new_file_name, base64_decode($image));
        }

        Meal::find($mealId)->update([
            'MealDescription' => $request->input('foodDescription') ?? '',
            'MealToday' => $request->input('todaySpecial') ?? false,
            'MealName' => $request->input('foodName'),
            'MealPrice' => $request->input('foodPrice'),
            'MealSoldOut' => $request->input('foodIsSoldOut') ?? false,
            'MenuTypeID' => $request->input('foodTypeID'),
            'MealCalorie' => $request->input('calories') ?? 0,
            'MealImagePath' => $new_file_name
        ]);

        $this->updateMealCache($storeId);
    }

    public function deleteMeal($storeId, $mealId)
    {
        $meal = Meal::find($mealId);

        if ($meal == null) {
            return \response('MealID not found', 422);
        }

        $meal->del_flag = true;
        $meal->save();

        $this->updateMealCache($storeId);
    }

    public function changeMenuType(Request $request, $storeId, $mealId)
    {
        $meal = Meal::find($mealId);
        $menuType = MenuType::find($request->input('menuTypeID'));

        if ($meal == null || $menuType == null) {
            return \response('MealID or MenuTypeID not found', 422);
        }

        $meal->MenuTypeID = $request->input('menuTypeID');
        $meal->save();

        $this->updateMealCache($storeId);
    }

    public function changeMealStatus(Request $request, $storeId, $mealId)
    {
        $input = $request->all();
        $input['mealID'] = $mealId;

        $validator = Validator::make($input, [
            'mealID' => 'required|exists:meal',
            'mealSoldOut' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return \response($validator->errors(), 422);
        }

        $meal = Meal::find($mealId);
        $meal->MealSoldOut = $request->input('mealSoldOut');
        $meal->save();

        $this->updateMealCache($storeId);
    }

    public function searchMeal(Request $request)
    {
        $tokens = explode(' ', $request->input('searchString'));

        $allStoreID = $this->getAllStoreID();

        $mealCache = collect();

        foreach($allStoreID as $storeID)
        {
            $mealCache = $mealCache->merge($this->getMealCache($storeID));
        }

        $meals = $mealCache->filter(function ($item) use ($tokens) 
        {
            $num = 0;

            foreach ($tokens as $token) 
            {
                if (Str::contains($item->MealName, $token) || Str::contains($item->StoreName, $token) || Str::contains($item->MenuTypeName, $token)) 
                {
                    $num++;
                }
            }

            if($num == count($tokens))
            {
                return true;
            }
        });
        
        $collection = collect();

        foreach ($meals as $meal) 
        {
            $collection->push(collect([
                'id' => $meal->MealID,
                'storeId' => $meal->StoreID,
                'storeName' => $meal->StoreName,
                'name' => $meal->MealName,
                'price' => $meal->MealPrice,
                'imageUri' => $meal->MealImagePath,
                'isSoldOut' => $meal->MealSoldOut
                ])
            );
        }

        return $collection;
    }

    public function getAllStoreID()
    {
        $storeID = Cache::rememberForever('store', function () {
            return Store::all();
        })->pluck('StoreID');

        return $storeID;
    }

    public function getMealCache($storeId)
    {
        $mealCache = Cache::remember(self::MEAL_CACHE_KEY_PREFIX . $storeId, 60, function () use ($storeId) 
        {
            return Meal::where('del_flag', false)
                ->join('menutype', 'menutype.MenuTypeID', '=', 'meal.MenuTypeID')
                ->join('store', 'menuType.StoreID', '=', 'store.StoreID')
                ->where('store.StoreID', $storeId)
                ->get();
        });

        return $mealCache;
    }

    public function updateMealCache($storeId)
    {
        Cache::forget('self::MEAL_CACHE_KEY_PREFIX' . $storeId);
        $this->getMealCache($storeId);
    }
}
