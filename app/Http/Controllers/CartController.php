<?php

namespace App\Http\Controllers;

use App\User;
use App\Cart;
use App\Meal;
use App\Store;
use App\Flavor;
use App\FlavorType;
use App\CartFlavor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CartController extends Controller
{
    public function storeCartItem(Request $request, $userId)
    {
        //check user's validation
        $user = User::find($userId);

        if($user == null)
        {
            return \response('User is unvalid', 401);
        }
        else if($user->email_verified_at == null)
        {
            return \response('User is Unverified', 403);
        }

        //validation
        $validator = Validator::make($request->all(), [
            'storeID' => 'required|exists:store',
            'mealID' => 'required|exists:meal',
            'quantity' => 'required|integer|min:1|max:99',
            'flavors.*.flavorTypeID' => 'required|exists:flavortype',
            'flavors.*.flavorName' => 'required|exists:flavor',
        ]);
        
        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $flavorsData = FlavorType::join('flavor', 'flavor.FlavorTypeID', '=', 'flavortype.FlavorTypeID')->get();
        $flavorValidator = collect($request->input('flavors'))->groupBy('flavorTypeID');
        $flavorType = null;

        foreach($flavorValidator as $flavors)
        {
            $flavorType = $flavorsData->where('FlavorTypeID', $flavors[0]['flavorTypeID'])->first();

            if(!$flavorType->isMultiple && $flavors->count() > 1)
            {
                return \response("FlavorType " . $flavorType->FlavorTypeID . ' cannot have multiple flavors', 422);
            }
        }

        $meal = Meal::find($request->input('mealID'));
        $quantity = $request->input('quantity');

        if($meal->MealSoldOut)
        {
            return collect([
                'result' => false
            ]);
        }

        $flavors = collect($request->input('flavors')) ?? collect();

        //if the item is already in cart, add quantity

        $carts = Cart::where('UserID', $userId)
                    ->where('MealID', $meal->MealID)
                    ->leftjoin('cartflavor', 'cart.CartID', '=', 'cartflavor.CartID')
                    ->leftjoin('flavor', 'cartflavor.FlavorID', '=', 'flavor.FlavorID')
                    ->select('*', 'Cart.CartID')
                    ->get();

        $carts = $carts->groupBy('CartID');
        $exist = null;
        $flag = null;
        $updateCart = null;

        foreach($carts as $cart) 
        {
            $exist = true;

            foreach($flavors as $flavor)
            {
                $flag = $cart->contains(function ($value, $key) use ($flavor) {
                    return $value->FlavorTypeID == $flavor['flavorTypeID'] && $value->FlavorName == $flavor['flavorName'];
                });

                if(!$flag)
                {
                    $exist = false;
                    break;
                }
            }

            if($exist && !$flavors->isEmpty())
            {
                $updateCart = Cart::find($cart[0]->CartID);
                $updateCart->Amount = ($updateCart->Amount / $updateCart->Quantity) * ($updateCart->Quantity + $request->input('quantity'));
                $updateCart->Quantity += $request->input('quantity');
                $updateCart->save();

                return collect([
                    'result' => true
                ]);
            }
        }
        
        //add cartItem
        $newCart = Cart::create([
            'TypeName' => "",
            'FoodCourt' => "",
            'StoreName' => "",
            'DateTime' => Carbon::now(),
            'StoreID' => $request->input('storeID'),
            'MealID' => $meal->MealID,
            'UserID' => $userId,
            'Quantity' => $quantity,
            'Amount' => $meal->MealPrice * $quantity,
        ]);

        $flavorData = null;
        $extraPrice = 0;

        foreach($flavors as $flavor)
        {
            $flavorData = $flavorsData->where('FlavorTypeID', $flavor['flavorTypeID'])
                                        ->where('FlavorName', $flavor['flavorName'])
                                        ->first();

            $extraPrice += $flavorData->ExtraPrice;

            CartFlavor::create([
                'CartID' => $newCart->CartID,
                'FlavorTypeID' => $flavor['flavorTypeID'],
                'FlavorID' => $flavorData->FlavorID
            ]);
        }

        $newCart->Amount = $newCart->Amount + $extraPrice * $quantity;
        $newCart->save();

        return collect([
            'result' => true
        ]);
    }

    public function getCartItem($userId)
    {
        $collection = collect();

        $carts = Cart::where('UserID', $userId)
                    ->join('store', 'store.StoreID', '=', 'cart.StoreID')
                    ->join('meal', 'meal.MealID', '=', 'cart.MealID')
                    ->leftjoin('cartflavor', 'cartflavor.CartID', '=', 'cart.CartID')
                    ->leftjoin('flavor', 'cartflavor.FlavorID', '=', 'flavor.FlavorID')
                    ->select('*', 'cart.CartID')
                    ->get();

        $storesCartItems = $carts->groupBy('StoreID');
        $cartItemDatas = null;
        $cartItemsFlavors = null;
        $flavors = null;
        $cartItemTemp = null;

        foreach($storesCartItems as $storeCartItems)
        {   
            $cartItemDatas = collect();
            $cartItemsFlavors = $storeCartItems->groupBy('CartID');

            foreach($cartItemsFlavors as $cartItemflavors)
            {
                $flavors = collect();
                
                foreach($cartItemflavors as $flavor)
                {
                    if($flavor->FlavorTypeID == null)
                    {
                        break;
                    }

                    $flavors->push(
                        collect([
                            'flavorTypeID' => $flavor->FlavorTypeID,
                            'flavorID' => $flavor->FlavorID,
                            'flavorName' => $flavor->FlavorName,
                            'extraPrice' => $flavor->ExtraPrice
                        ])
                    );
                }

                $cartItemTemp = $cartItemflavors[0];

                $cartItemDatas->push(
                    collect([
                        'cartItemID' => $cartItemTemp->CartID,
                        'mealID' => $cartItemTemp->MealID,
                        'mealName' => $cartItemTemp->MealName,
                        'mealPrice' => $cartItemTemp->MealPrice,
                        'calories' => $cartItemTemp->MealCalorie,
                        'price' => $cartItemTemp->Amount,
                        'memo' => $cartItemTemp->Memo,
                        'quantity' => $cartItemTemp->Quantity,
                        'imageUri' => $cartItemTemp->MealImagePath,
                        'flavors' => $flavors
                    ])
                );
            }

            $collection->push(
                collect([
                    'storeID' => $storeCartItems[0]['StoreID'],
                    'storeName' => $storeCartItems[0]['StoreName'],
                    'offset' => $storeCartItems[0]['Offset'],
                    'cartItem' => $cartItemDatas,
            ]));
        }
        
        return $collection;
    }

    public function updateCartItem(Request $request, $userId, $cartItemId)
    {
        $input = $request->all();
        $input['userID'] = $userId;
        $input['cartItemID'] = $cartItemId;

        $validator = Validator::make($input, [
            'userID' => 'required|exists:users,id',
            'cartItemID' => 'required|exists:cart,CartID',
            'quantity' => 'integer|min:1|max:99',
            'flavors.*.flavorTypeID' => 'required|exists:flavortype',
            'flavors.*.flavorName' => 'required|exists:flavor',
        ]);
        
        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $flavorsData = FlavorType::join('flavor', 'flavor.FlavorTypeID', '=', 'flavortype.FlavorTypeID')->get();
        $flavorValidator = collect($request->input('flavors'))->groupBy('flavorTypeID');
        $flavorType = null;

        foreach($flavorValidator as $flavors)
        {
            $flavorType = $flavorsData->where('FlavorTypeID', $flavors[0]['flavorTypeID'])->first();
    
            if(!$flavorType->isMultiple && $flavors->count() > 1)
            {
                return \response("FlavorType " . $flavorType->FlavorTypeID . ' cannot have multiple flavors', 422);
            }
        }
               
        $cart = Cart::find($cartItemId);
        $quantity = $request->input('quantity') ?? $cart->Quantity;
        $flavors = $request->input('flavors') ?? CartFlavor::where('CartID', $cart->CartID)->get()->toArray();
        $flavorData = null;
        $extraPrice = 0;

        if($request->input('flavors') != null)
        {
            CartFlavor::where('CartID',$cartItemId)->delete();

            foreach($flavors as $flavor)
            {
                $flavorData = $flavorsData->where('FlavorTypeID', $flavor['flavorTypeID'])
                                            ->where('FlavorName', $flavor['flavorName'])
                                            ->first();
                                
                $extraPrice += $flavorData->ExtraPrice;
    
                CartFlavor::create([
                    'CartID' => $cartItemId,
                    'FlavorTypeID' => $flavor['flavorTypeID'],
                    'FlavorID' => $flavorData->FlavorID
                ]);
            }
        }
        else
        {
            foreach($flavors as $flavor)
            {
                $flavorData = $flavorsData->where('FlavorTypeID', $flavor['FlavorTypeID'])
                                            ->where('FlavorID', $flavor['FlavorID'])
                                            ->first();
                                
                $extraPrice += $flavorData->ExtraPrice;
            }
        }
       
        $meal = Cart::where("CartID" ,$cartItemId)
                    ->join('meal', 'Meal.MealID', '=', "cart.MealID")
                    ->get()
                    ->first();

        $cart->update([
            'Quantity' => $quantity,
            'Memo' => $request->input('memo')?? "",
            'Amount' => ($meal->MealPrice + $extraPrice) * $quantity
        ]);
    }

    public function deleteCartItem($userId, $cartItemId)
    {
        $temp = Cart::find($cartItemId);

        if($temp == null)
        {
            return \response('CartItemID not found', 422);
        }

        $temp->delete();
    }

    public function deleteAllCartItem($userId)
    {
        $user = User::find($userId);

        if($user == null)
        {
            return \response('UserID not found', 422);
        }

        Cart::where('UserID', $userId)->delete();
    }
}
