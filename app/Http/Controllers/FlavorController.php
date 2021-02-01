<?php

namespace App\Http\Controllers;

use App\Flavor;
use App\FlavorType;
use App\MealFlavor;
use App\Meal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlavorController extends Controller
{
    public function getFlavors($storeId, $mealId)
    {   
        $data = MealFlavor::where('MealID', $mealId)
            ->join('flavorType','mealFlavor.FlavorTypeID', '=', "flavorType.FlavorTypeID")
            ->join('flavor', 'flavorType.flavorTypeID', '=', 'flavor.flavorTypeID')
            ->get();
    
        if($data->isEmpty())
        {
            return \response('', 204);
        }

        $collection = collect();
        $flavorsName = null;
        $flavorTypes = $data->groupBy('FlavorTypeID');
        
        foreach($flavorTypes as $flavorType)
        {
            $flavorsName = collect();

            foreach($flavorType as $flavor)
            {
                $flavorsName->push([
                    'flavorID'=> $flavor->FlavorID,
                    'flavorName' => $flavor->FlavorName,
                    'extraPrice' => $flavor->ExtraPrice
                ]);
            }

            $flavorTypeTemp = $flavorType[0];

            $temp = collect([
                'id' => $flavorTypeTemp['FlavorTypeID'],
                'name' => $flavorTypeTemp['FlavorTypeName'],
                'isRequired' => $flavorTypeTemp['isRequired'],
                'isMultiple' => $flavorTypeTemp['isMultiple'],
                'items' => $flavorsName
            ]);

            $collection->push($temp);
        }

        return collect([
            'flavors' => $collection
        ]);
    }

    public function updateFlavors(Request $request, $storeId, $mealId, $flavorTypeId)
    {
        $input = array();
        $input['items'] = $request->all();
        $input['storeID'] = $storeId;
        $input['mealID'] = $mealId;
        $input['flavorTypeID'] = $flavorTypeId;

        $validator = Validator::make($input, [
            'storeID' => 'required|exists:store',
            'mealID' => 'required|exists:meal',
            'flavorTypeID' => 'required|exists:flavortype',
            'items.*.extraPrice' => 'integer|min:0',
            'items.*.flavorName' => 'string',
            'items.*.flavorID' => 'exists:flavor,FlavorID'
        ]);
        
        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $data = array();
        $items = $request->json()->all();
        $change = '';

        foreach($items as $item)
        {
            $change = $item['change'];

            if($change == 'C')
            {
                $data[] = [
                    'FlavorName' => $item['flavorName'],
                    'ExtraPrice' => $item['extraPrice'] ?? 0,
                    'FlavorTypeID'=> $flavorTypeId
                ];
            }
            else if($change == 'U')
            {
                Flavor::find($item['flavorID'])->update([
                    'FlavorName' => $item['flavorName'],
                    'ExtraPrice' => $item['extraPrice'] ?? 0,
                    'FlavorTypeID'=> $flavorTypeId
                ]);
                
            }
            else if($change == 'D')
            {
                Flavor::find($item['flavorID'])->delete();
            }
        }

        Flavor::insert($data);
    }
}
