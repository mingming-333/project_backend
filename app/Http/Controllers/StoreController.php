<?php

namespace App\Http\Controllers;

use Storage;
use App\Store;
use App\StoreBusinessDay;
use App\StoreBusinessHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StoreController extends Controller
{
    private const BUSINESS_HOUR_CACHE_KEY = 'businessHour';
    private const BUSINESS_DAY_CACHE_KEY = 'businessDay';

    public function addStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'storeName' => 'required|string',
            'foodCourtID' => 'required|exists:foodcourt',
            'superUserID' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return \response($validator->errors(), 422);
        }

        Store::create([
            'StoreName' => $request->input('storeName'),
            'StoreTheme' => 0,
            'StoreDescription' => $request->input('storeDescription') ?? '',
            'FoodCourtID' => $request->input('foodCourtID'),
            'SuperUserID' => $request->input('superUserID'),
            'StoreImagePath' => '',
        ]);
    }

    public function deleteStore($storeId)
    {
        $temp = Store::find($storeId);

        if ($temp == null) {
            return \response('StoreID not found', 422);
        }
    }

    public function getStoreInfo($storeId)
    {
        $store = Store::find($storeId);

        if ($store == null) {
            return \response('StoreID not found', 422);
        }

        $businessHourData = Cache::remember(self::BUSINESS_HOUR_CACHE_KEY, 3600, function () {
            return StoreBusinessHour::all();
        })
        ->where('StoreID', $storeId)
        ->sortBy('BusinessHour');

        $openTime = $businessHourData->where('StoreState', 1)->values();
        $closeTime = $businessHourData->where('StoreState', 0)->values();
        $size = count($openTime);

        $businessHour = collect();

        for($i = 0; $i < $size; $i++)
        {
            $businessHour->push(collect([
                'start' => $openTime[$i]['BusinessHour'],
                'end' => $closeTime[$i]['BusinessHour'],
            ]));
        }

        $businessDayData = Cache::remember(self::BUSINESS_DAY_CACHE_KEY, 3600, function () {
            return StoreBusinessDay::all();
        })
        ->where('StoreID', $storeId)
        ->pluck('BusinessDay');

        return collect([
            'storeName' => $store->StoreName,
            'storeImg' => $store->StoreImagePath,
            'offset' => $store->Offset,
            'isStoreOpen' => $store->IsOpen,
            'businessHour' => $businessHour,
            'businessDay' => $businessDayData
        ]);
    }

    public function updateStoreStatus(Request $request, $storeId)
    {
        $validator = Validator::make($request->all(), [
            'isOpen' => 'required|boolean'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $store = Store::find($storeId);

        if ($store == null) {
            return \response('StoreID not found', 422);
        }

        $store->IsOpen = $request->input('isOpen');
        $store->save();
    }

    public function updateStoreInfo(Request $request, $storeId)
    {
        $validator = Validator::make($request->all(), [
            'storeName' => 'string',
            'offset' => 'integer|min:0',
            'businessHour.*.start' => 'required',
            'businessHour.*.end' => 'required',
            'businessDay.*' => 'integer|in:1,2,3,4,5,6,7'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $store = Store::find($storeId);

        if ($store == null) {
            return \response('StoreID not found', 422);
        }

        // update business time
        if($request->has('businessHour'))
        {
            $newBusinessHours = $request->input('businessHour');

            StoreBusinessHour::where('StoreID', $storeId)->delete();
            $data = array();

            foreach($newBusinessHours as $hour)
            {
                if($hour['end'] > $hour['start'])
                {
                    $data[] = [
                        'StoreID' => $storeId,
                        'StoreState' => 1,
                        'BusinessHour' => $hour['start']
                    ];

                    $data[] = [
                        'StoreID' => $storeId,
                        'StoreState' => 0,
                        'BusinessHour' => $hour['end']
                    ];
                }
                else
                {
                    return \response('Business hour has error', 422);
                }
            }

            StoreBusinessHour::insert($data);
            Cache::put(self::BUSINESS_HOUR_CACHE_KEY, StoreBusinessHour::all(), 3600);
        }

        if($request->has('businessDay'))
        {
            $newBusinessDays = $request->input('businessDay');
            StoreBusinessDay::where('StoreID', $storeId)->delete();
            $data = array();

            foreach($newBusinessDays as $day)
            {
                $data[] = [
                    'StoreID' => $storeId,
                    'BusinessDay' => $day
                ];
            }

            StoreBusinessDay::insert($data);
            Cache::put(self::BUSINESS_DAY_CACHE_KEY, StoreBusinessDay::all(), 3600);
        }

        // store image process 
        $new_filename = $store->StoreImagePath;

        if ($request->has('storeImg')) {
            $new_filename = date('YmdHis') . '_' . $store->StoreID . '_' . $store->StoreName . '.' . 'png';
            $image = $request->storeImg;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            Storage::put('public/store/' . $new_filename, base64_decode($image));
            $old_filename = $store->StoreImagePath;

            Storage::delete('public/store/' . $old_filename);
        }

        $store->update([
            'StoreName' => $request->input('storeName') ?? $store->StoreName,
            'StoreImagePath' => $new_filename,
            'Offset' => $request->input('offset') ?? $store->Offset
        ]);

        return collect([
            'imagePath' => $new_filename
        ]);
    }

    public function changeStoreStateBySchedule()
    {
        $businessDayData = Cache::remember(self::BUSINESS_DAY_CACHE_KEY, 3600, function () {
            return StoreBusinessDay::all();
        });

        $businessHourData = Cache::remember(self::BUSINESS_HOUR_CACHE_KEY, 3600, function () {
            return StoreBusinessHour::all();
        });

        $store = $businessDayData->where('BusinessDay', Carbon::now()->weekday())->pluck('StoreID');
        $updateTimes = $businessHourData->whereIn('StoreID', $store)->where('BusinessHour', Carbon::now()->format('H:i:00'));

        foreach($updateTimes as $businessHour)
        {  
            Store::find($businessHour->StoreID)->update([
                'IsOpen' => $businessHour->StoreState
            ]);
        }
    }
}
