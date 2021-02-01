<?php

namespace App\Http\Controllers;

use App\MenuType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuTypeController extends Controller
{
    public function getAllMenuType($storeId) 
    {
        $menuTypes = MenuType::where('StoreID', $storeId)->get();

        if($menuTypes->isEmpty())
        {
            return \response('StoreID not found', 422);
        }

        $collection = collect();

        foreach($menuTypes as $menuType)
        {
            $temp = collect([
                'menuTypeID' => $menuType->MenuTypeID,
                'menuTypeName' => $menuType->MenuTypeName
            ]);

            $collection->push($temp);
        }

        return $collection->toJson();
    }

    public function storeMenuType(Request $request, $storeId)
    {
        $input = $request->all();
        $input['storeID'] = $storeId;

        $validator = Validator::make($input, [
            'MenuTypeName' => 'required|string',
            'storeID' => 'required|exists:store'
        ]);

        if($validator->fails())
        {   
            return \response($validator->errors(), 422);
        }

        MenuType::create([
            'MenuTypeName' => $request->input('MenuTypeName'),
            'StoreID' => $storeId
        ]);
    }

    public function updateMenuType(Request $request, $storeId)
    {
        $input = $request->all();
        $input['storeID'] = $storeId;

        $validator = Validator::make($input, [
            'menuTypeID' => 'required|exists:menuType',
            'menuTypeName' => 'required|string',
            'storeID' => 'required|exists:store'
        ]);

        if($validator->fails())
        {   
            return \response($validator->errors(), 422);
        }

        $menutype = MenuType::find($request->input('menuTypeID'));
        $menutype->MenuTypeName = $request->input('menuTypeName');
        $menutype->save();
    }

    public function deleteMenuType($storeId, $menuTypeId)
    {
        $temp = MenuType::find($menuTypeId);

        if($temp == null)
        {
            return \response('MenuTypeID not found', 422);
        }
        
        $temp->delete();
    }
}
