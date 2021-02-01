<?php

namespace App\Http\Controllers;

use App\MealFlavor;
use App\FlavorType;
use App\Flavor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlavorTypeController extends Controller
{
    public function storeFlavorType(Request $request, $storeId, $mealId)
    {
        $data = $request->all();
        $data['storeID'] = $storeId;
        $data['mealID'] = $mealId;

        $validator = Validator::make($data, [
            'storeID' => 'required|exists:store',
            'mealID' => 'required|exists:meal',
            'name' => 'required|string',
            'isRequired' => 'required|boolean',
            'isMultiple' => 'required|boolean',
            'items.*.extraPrice' => 'required|integer|min:0',
            'items.*.flavorName' => 'required|string'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $flavorType = FlavorType::create([
            'FlavorTypeName' => $request->input('name'),
            'StoreID' => $storeId
        ]);
        
        MealFlavor::create([
            'MealID' => $mealId,
            "FlavorTypeID" =>$flavorType->FlavorTypeID
        ]);

        $items = $request->input('items');
        $data = array();
        
        foreach($items as $item)
        {
            $data[] = [
                'FlavorName' => $item['flavorName'],
                'ExtraPrice' => $item['extraPrice'],
                'FlavorTypeID'=> $flavorType->FlavorTypeID
            ];
        }

        Flavor::insert($data);
    }

    public function updateFlavorType(Request $request, $storeId, $mealId, $flavorTypeId)
    {
        $data = $request->all();
        $data['storeID'] = $storeId;
        $data['mealID'] = $mealId;
        $data['flavorTypeID'] = $flavorTypeId;

        $validator = Validator::make($data, [
            'storeID' => 'required|exists:store',
            'mealID' => 'required|exists:meal',
            'flavorTypeID' => 'required|exists:flavortype',
            'name' => 'required|string',
            'isRequired' => 'required|boolean',
            'isMultiple' => 'required|boolean'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        FlavorType::find($flavorTypeId)->update([
            'FlavorTypeName' => $data['name'],
            'isRequired' => $data['isRequired'],
            'isMultiple' => $data['isMultiple']
        ]);
    }

    public function deleteFlavorType($storeId, $mealId, $flavorTypeId)
    {
        $temp = FlavorType::find($flavorTypeId);

        if($temp == null)
        {
            return \response('FlavorTypeID not found', 422);
        }
        
        $temp->delete();
    }
}
