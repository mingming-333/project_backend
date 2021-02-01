<?php

namespace App\Http\Controllers;

use App\User;
use App\Store;
use App\Meal;
use App\Order;
use App\Cart;
use App\Flavor;
use App\FlavorType;
use App\CartFlavor;
use App\OrderItem;
use App\OrderItemFlavor;
use App\UserDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OrderController extends Controller
{
    private const NEW_ORDER_CACHE_PREFIX = 'new_order_';

    private const ORDER_STATUS_MESSAGE = array(
        0 => '有新訂單進來哦，請前往確認',
        1 => '已接單，餐點製作中',
        2 => '已完成，可前往取餐',
        3 => '已被放入歷史訂單中',
        4 => '逾時未取餐',
        5 => '過久未接單，訂單已被取消',
        6 => '已刊登訂單至外送接單頁面，等待外送員接單',
        7 => '外送員已接單，等待餐廳接單',
        99 => '店家拒絕接單，訂單已被取消',
        -1 => '有狀態更新，請前往確認'
    );

    public function storeOrderFromCart(Request $request, $userId)
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

        //check data validation
        $validator = Validator::make($request->all(), [
            '*.storeID' => 'required|exists:store',
            '*.memo' => 'nullable|string',
            '*.estimatedTime' => 'date',
            '*.isTakeOut' => 'boolean'
            ]);
            
        $orderDatas = collect($request->all());

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $data = Cart::where('UserID', $userId)
                    ->leftJoin('cartflavor', 'cart.CartID', '=', 'cartflavor.CartID')
                    ->select('*', 'cart.CartID')
                    ->get();

        if ($data->isEmpty()) {
            return \response("", 204);
        }

        $stores = $data->groupBy('StoreID');
        $storeID = null;
        $orderData = null;
        $orderNumber = null;
        $orderItems = null;
        $flavorData = null;
        $newOrder = null;
        $newOrderItem = null;
        $newOrderRecord = null;
        $key = '';
        
        foreach ($stores as $store) 
        {
            $storeID = $store[0]->StoreID;
            $orderData = $orderDatas->where('storeID', $storeID)->first();
            $orderNumber = Order::where('StoreID', $storeID)
                                ->whereDate('DateTime', Carbon::today())
                                ->count();
            
            if(($orderData->$isTakeOut == 0 || $orderData->$isTakeOut == 1) && $orderData->$isDelivery ==0)
            {
                 $newOrder = Order::create([
                     'OrderNumber' => ++$orderNumber,
                     'Price' => $store->unique('CartID')->sum('Amount'),
                     'Status' => 0,
                     'IsTakeOut' => $orderData->$isTakeOut,
                     'IsDelivery' => $orderData->$isDelivery,
                     'Memo' =>  $orderData['memo'] ?? "",
                     'StoreID' => $storeID,
                     'CustomerID' => $userId,
                     'DateTime' => Carbon::now(),
                     'UpdateTime' => Carbon::now(),
                     'EstimatedTime' => $orderData['estimatedTime'] ?? '0000-00-00 00:00:00'
                 ]);
            }
            
            //delivery
            else if($orderData->$isTakeOut == 1 && $orderData->$isDelivery ==1) 
            {
                $newOrder = Order::create([
                    'OrderNumber' => ++$orderNumber,
                    'Price' => $store->unique('CartID')->sum('Amount'),
                    'Status' => 6,
                    'IsTakeOut' => $orderData->$isTakeOut,
                    'IsDelivery' => $orderData->$isDelivery,
                    'Memo' =>  $orderData['memo'] ?? "",
                    'StoreID' => $storeID,
                    'CustomerID' => $userId,
                    'DateTime' => Carbon::now(),
                    'UpdateTime' => Carbon::now(),
                    'EstimatedTime' => $orderData['estimatedTime'] ?? '0000-00-00 00:00:00',
                    'ServiceFee' => $orderData->$ServiceFee,
                    'Destination' => $orderData->$Destination,
                    'TotalAmount' => $orderData->$TotalAmount
                ]);
            }

            $orderItems = $store->groupBy('CartID');

            foreach($orderItems as $orderItem) 
            {
                $newOrderItem = OrderItem::create([
                    'TypeName' => "",
                    'FoodCourt' => "",
                    'StoreName' => "",
                    'DateTime' => Carbon::now(),
                    'OrderID' => $newOrder->OrderID,
                    'MealID' => $orderItem[0]->MealID,
                    'Quantity' => $orderItem[0]->Quantity,
                    'Amount' => $orderItem[0]->Amount,
                    'Memo' => $orderItem[0]->Memo ?? ""
                ]);

                foreach ($orderItem as $flavor) 
                {
                    if ($flavor->CartFlavorID == null) 
                    {
                        break;
                    }

                    OrderItemFlavor::create([
                        'OrderItemID' => $newOrderItem->OrderItemID,
                        'FlavorID' => $flavor->FlavorID
                    ]);
                }
            }

            // order notification old version
            // $key = self::NEW_ORDER_CACHE_PREFIX . $newOrder->StoreID;
            // $newOrderRecord = Cache::pull($key) ?? collect();

            // $newOrderRecord->push(collect([
            //     'OrderID' => $newOrder->OrderID,
            //     'OrderNumber' => $newOrder->OrderNumber,
            //     'StoreID' => $newOrder->StoreID,
            //     'Status' => $newOrder->Status
            // ]));
    
            // Cache::forever($key, $newOrderRecord);

            // send order notification by firebase
            $storeUser = Store::find($storeID)->SuperUserID;
            $response = $this->sendOrderNotificationToFirebaseToRestaurant($storeUser, $newOrder);
        }

        Cart::where('UserID', $userId)->delete();
        return $response;
    }

    public function getStoreCurrentOrders($storeId)
    {
        $data = Order::where('orders.StoreID', $storeId)
            ->whereIn('Status', [0, 1, 2])
            ->join('orderitem', 'orderitem.OrderID', '=', 'orders.OrderID')
            ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
            ->leftJoin('orderitemflavor', 'orderitem.OrderItemID', '=', 'orderitemflavor.OrderItemID')
            ->leftJoin('flavor', 'orderitemflavor.FlavorID', '=', 'flavor.FlavorID')
            ->leftJoin('flavortype', 'flavortype.FlavorTypeID', '=', 'flavor.FlavorTypeID')
            ->select('*', 'orderitem.OrderItemID', 'orders.DateTime as OrderDate')
            ->orderBy('orders.OrderID', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return \response("", 204);
        }

        $collection = collect();
        $items = null;
        $count = 0;
        $flavors = null;

        $orders = $data->groupBy('OrderID');

        foreach ($orders as $order) {
            $items = collect();
            $count = 0;

            $orderItems = $order->groupBy('OrderItemID');

            foreach ($orderItems as $orderItem) {
                if ($count >= 3) {
                    break;
                }

                $flavors = collect();

                foreach ($orderItem as $orderItemFlavor) {
                    if ($orderItemFlavor->FlavorTypeID == null) {
                        break;
                    }

                    $flavors->push(
                        collect([
                            'flavorType' => $orderItemFlavor->FlavorTypeName,
                            'flavor' => $orderItemFlavor->FlavorName,
                        ])
                    );
                }


                $temp = $orderItem[0];

                $items->push(
                    collect([
                        'id' => $temp->OrderItemID,
                        'name' => $temp->MealName,
                        'memo' => $temp->Memo,
                        'quantity' => $temp->Quantity,
                        'mealPrice' => $temp->MealPrice,
                        'flavors' => $flavors
                    ])
                );

                ++$count;
            }

            $collection->push(
                collect([
                    'id' => $temp->OrderID,
                    'orderNumber' => $temp->OrderNumber,
                    'status' => $temp->Status,
                    'orderDate' => $temp->OrderDate,
                    'estimatedTime' => $temp->EstimatedTime,
                    'orderPrice' => $temp->Price,
                    'isTakeOut' => $temp->IsTakeOut,
                    'orderItems' => $items
                ])
            );
        }

        return $collection;
    }

    public function getStoreHistoryOrders(Request $request, $storeId)
    {
        // version 2
        $date = [
            $request->date . ' 00:00:00',
            $request->date . ' 23:59:59'
        ];
        $data = Order::where('orders.StoreID', $storeId)
            ->whereIn('Status', [3, 4, 99])
            ->whereBetween('orders.DateTime', $date)
            ->join('orderitem', 'orderitem.OrderID', '=', 'orders.OrderID')
            ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
            ->leftJoin('orderitemflavor', 'orderitem.OrderItemID', '=', 'orderitemflavor.OrderItemID')
            ->leftJoin('flavor', 'orderitemflavor.FlavorID', '=', 'flavor.FlavorID')
            ->leftJoin('flavortype', 'flavortype.FlavorTypeID', '=', 'flavor.FlavorTypeID')
            ->select('*', 'orderitem.OrderItemID', 'orders.DateTime as OrderDate')
            ->orderBy('orders.OrderID', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return \response("", 204);
        }

        $collection = collect();
        $items = null;
        $count = 0;
        $flavors = null;

        $orders = $data->groupBy('OrderID');

        foreach ($orders as $order) {
            $items = collect();
            $count = 0;

            $orderItems = $order->groupBy('OrderItemID');

            foreach ($orderItems as $orderItem) {
                if ($count >= 3) {
                    break;
                }

                $flavors = collect();

                foreach ($orderItem as $orderItemFlavor) {
                    if ($orderItemFlavor->FlavorTypeID == null) {
                        break;
                    }

                    $flavors->push(
                        collect([
                            'flavorType' => $orderItemFlavor->FlavorTypeName,
                            'flavor' => $orderItemFlavor->FlavorName,
                        ])
                    );
                }


                $temp = $orderItem[0];

                $items->push(
                    collect([
                        'id' => $temp->OrderItemID,
                        'name' => $temp->MealName,
                        'memo' => $temp->Memo,
                        'quantity' => $temp->Quantity,
                        'mealPrice' => $temp->MealPrice,
                        'flavors' => $flavors
                    ])
                );

                ++$count;
            }

            $collection->push(
                collect([
                    'id' => $temp->OrderID,
                    'orderNumber' => $temp->OrderNumber,
                    'status' => $temp->Status,
                    'orderDate' => $temp->OrderDate,
                    'orderPrice' => $temp->Price,
                    'isTakeOut' => $temp->IsTakeOut,
                    'orderItems' => $items
                ])
            );
        }

        return $collection;
    }

    public function getCustomerCurrentOrders($customerId)
    {
        // version 2
        $data = Order::where('orders.CustomerID', $customerId)
            ->whereIn('Status', [0, 1, 2, 6, 7])
            ->join('orderitem', 'orderitem.OrderID', '=', 'orders.OrderID')
            ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
            ->join('store', 'store.StoreID', '=', 'orders.StoreID')
            ->leftJoin('orderitemflavor', 'orderitem.OrderItemID', '=', 'orderitemflavor.OrderItemID')
            ->leftJoin('flavor', 'orderitemflavor.FlavorID', '=', 'flavor.FlavorID')
            ->leftJoin('flavortype', 'flavortype.FlavorTypeID', '=', 'flavor.FlavorTypeID')
            ->select('*', 'orderitem.OrderItemID', 'orders.DateTime as OrderDate')
            ->orderBy('orders.OrderID', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return \response("", 204);
        }

        $collection = collect();
        $items = null;
        $count = 0;
        $flavors = null;
        $flavorsPrice = 0;
        $temp = null;

        $orders = $data->groupBy('OrderID');

        foreach ($orders as $order) {
            $items = collect();
            $count = 0;

            $orderItems = $order->groupBy('OrderItemID');

            foreach ($orderItems as $orderItem) {
                
                // only show less than or equal 3 items
                if ($count >= 3) {
                    break;
                }

                $flavors = collect();
                $flavorsPrice = 0;

                foreach ($orderItem as $orderItemFlavor) {
                    if ($orderItemFlavor->FlavorTypeID == null) {
                        break;
                    }

                    $flavors->push(
                        collect([
                            'flavorType' => $orderItemFlavor->FlavorTypeName,
                            'flavor' => $orderItemFlavor->FlavorName,
                        ])
                    );

                    $flavorsPrice += $orderItemFlavor->ExtraPrice;
                }


                $temp = $orderItem[0];

                $items->push(
                    collect([
                        'id' => $temp->OrderItemID,
                        'name' => $temp->MealName,
                        'memo' => $temp->Memo,
                        'quantity' => $temp->Quantity,
                        'mealPrice' => $temp->MealPrice,
                        'flavors' => $flavors,
                        'amount' => $temp->MealPrice + $flavorsPrice,
                    ])
                );

                ++$count;
            }

            $collection->push(
                collect([
                    'id' => $temp->OrderID,
                    'orderNumber' => $temp->OrderNumber,
                    'store' => $temp->StoreName,
                    'status' => $temp->Status,
                    'orderDate' => $temp->OrderDate,
                    'estimatedTime' => $temp->EstimatedTime,
                    'orderPrice' => $temp->Price,
                    'isTakeOut' => $temp->IsTakeOut,
                    'orderItems' => $items
                ])
            );
        }

        return $collection;
    }

    public function getCustomerHistoryOrders($customerId)
    {
        // version 2
        $data = Order::where('orders.CustomerID', $customerId)
            ->where('Status', 3)
            ->join('orderitem', 'orderitem.OrderID', '=', 'orders.OrderID')
            ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
            ->join('store', 'store.StoreID', '=', 'orders.StoreID')
            ->leftJoin('orderitemflavor', 'orderitem.OrderItemID', '=', 'orderitemflavor.OrderItemID')
            ->leftJoin('flavor', 'orderitemflavor.FlavorID', '=', 'flavor.FlavorID')
            ->leftJoin('flavortype', 'flavortype.FlavorTypeID', '=', 'flavor.FlavorTypeID')
            ->select('*', 'orderitem.OrderItemID', 'orders.DateTime as OrderDate')
            ->orderBy('orders.OrderID', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return \response("", 204);
        }

        $collection = collect();
        $items = null;
        $flavors = null;
        $flavorsPrice = 0;
        $count = 0;

        $orders = $data->groupBy('OrderID');

        foreach ($orders as $order) {
            $items = collect();
            $count = 0;

            $orderItems = $order->groupBy('OrderItemID');

            foreach ($orderItems as $orderItem) {
                if ($count >= 3) {
                    break;
                }

                $flavors = collect();
                $flavorsPrice = 0;
                
                foreach ($orderItem as $orderItemFlavor) {
                    if ($orderItemFlavor->FlavorTypeID == null) {
                        break;
                    }

                    $flavors->push(
                        collect([
                            'flavorType' => $orderItemFlavor->FlavorTypeName,
                            'flavor' => $orderItemFlavor->FlavorName,
                        ])
                    );

                    $flavorsPrice += $orderItemFlavor->ExtraPrice;
                }


                $temp = $orderItem[0];

                $items->push(
                    collect([
                        'id' => $temp->OrderItemID,
                        'name' => $temp->MealName,
                        'memo' => $temp->Memo,
                        'quantity' => $temp->Quantity,
                        'mealPrice' => $temp->MealPrice,
                        'flavors' => $flavors,
                        'amount' => $temp->MealPrice + $flavorsPrice,
                    ])
                );

                ++$count;
            }

            $collection->push(
                collect([
                    'id' => $temp->OrderID,
                    'orderNumber' => $temp->OrderNumber,
                    'store' => $temp->StoreName,
                    'status' => $temp->Status,
                    'orderDate' => $temp->OrderDate,
                    'orderPrice' => $temp->Price,
                    'isTakeOut' => $temp->IsTakeOut,
                    'orderItems' => $items
                ])
            );
        }

        return $collection;
    }

    public function getStoreOrderDetail($orderId)
    {
        $data = Order::where('orders.OrderID', $orderId)
            ->join('orderitem', 'orderitem.OrderID', '=', 'orders.OrderID')
            ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
            ->leftJoin('orderitemflavor', 'orderitem.OrderItemID', '=', 'orderitemflavor.OrderItemID')
            ->leftJoin('flavor', 'orderitemflavor.FlavorID', '=', 'flavor.FlavorID')
            ->leftJoin('flavortype', 'flavortype.FlavorTypeID', '=', 'flavor.FlavorTypeID')
            ->select('*', 'orders.Memo', 'orderitem.OrderItemID', 'orders.DateTime as OrderDate')
            ->get();

        if ($data->isEmpty()) 
        {
            return \response("", 204);
        }

        $userTotalOrder = Order::where('orders.CustomerID', $data[0]->CustomerID)
            ->whereIn('Status', [3, 4])
            ->get();

        $totalOrderCount = $userTotalOrder->Count();
        $completeOrder = $userTotalOrder->where('Status', 3)->Count();

        $items = collect();
        $flavors = null;
        $temp = null;

        $orderItems = $data->groupBy('OrderItemID');

        foreach ($orderItems as $orderItem) {
            $flavors = collect();

            foreach ($orderItem as $orderItemFlavor) {
                if ($orderItemFlavor->FlavorTypeID == null) {
                    break;
                }

                $flavors->push(
                    collect([
                        'flavorType' => $orderItemFlavor->FlavorTypeName,
                        'flavor' => $orderItemFlavor->FlavorName,
                        'extraPrice' => $orderItemFlavor->ExtraPrice
                    ])
                );
            }

            $temp = $orderItem[0];

            $items->push(
                collect([
                    'id' => $temp->OrderItemID,
                    'name' => $temp->MealName,
                    'quantity' => $temp->Quantity,
                    'mealPrice' => $temp->MealPrice,
                    'flavors' => $flavors
                ])
            );
        }

        return collect([
            'id' => $temp->OrderID,
            'orderNumber' => $temp->OrderNumber,
            'status' => $temp->Status,
            'orderDate' => $temp->OrderDate,
            'estimatedTime' =>$temp->EstimatedTime,
            'orderMemo' => $temp->Memo,
            'orderPrice' => $temp->Price,
            'isTakeOut' => $temp->IsTakeOut,
            'totalOrder' => $totalOrderCount,
            'completeOrder' => $completeOrder,
            'orderItems' => $items
        ]);
    }

    public function getCustomerOrderDetail($orderId)
    {
        // version 2
        $data = Order::where('orders.OrderID', $orderId)
            ->join('orderitem', 'orderitem.OrderID', '=', 'orders.OrderID')
            ->join('store', 'store.StoreID', '=', 'orders.StoreID')
            ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
            ->leftJoin('orderitemflavor', 'orderitem.OrderItemID', '=', 'orderitemflavor.OrderItemID')
            ->leftJoin('flavor', 'orderitemflavor.FlavorID', '=', 'flavor.FlavorID')
            ->leftJoin('flavortype', 'flavortype.FlavorTypeID', '=', 'flavor.FlavorTypeID')
            ->select('*', 'orders.Memo', 'orderitem.OrderItemID', 'orders.DateTime as OrderDate')
            ->get();

        if ($data->isEmpty()) {
            return \response("", 204);
        }

        $items = collect();
        $flavors = null;
        $flavorsPrice = 0;
        $temp = null;

        $orderItems = $data->groupBy('OrderItemID');

        foreach ($orderItems as $orderItem) {
            
            $flavors = collect();
            $flavorsPrice = 0;

            foreach ($orderItem as $orderItemFlavor) {
                if ($orderItemFlavor->FlavorTypeID == null) {
                    break;
                }

                $flavors->push(
                    collect([
                        'flavorType' => $orderItemFlavor->FlavorTypeName,
                        'flavor' => $orderItemFlavor->FlavorName,
                        'extraPrice' => $orderItemFlavor->ExtraPrice
                    ])
                );

                $flavorsPrice += $orderItemFlavor->ExtraPrice;
            }

            $temp = $orderItem[0];

            $items->push(
                collect([
                    'id' => $temp->OrderItemID,
                    'name' => $temp->MealName,
                    'quantity' => $temp->Quantity,
                    'mealPrice' => $temp->MealPrice,
                    'flavors' => $flavors,
                    'amount' => $temp->MealPrice + $flavorsPrice
                ])
            );
        }

        return collect([
            'id' => $temp->OrderID,
            'orderNumber' => $temp->OrderNumber,
            'store' => $temp->StoreName,
            'status' => $temp->Status,
            'orderDate' => $temp->OrderDate,
            'estimatedTime' => $temp->EstimatedTime,
            'orderMemo' => $temp->Memo,
            'orderPrice' => $temp->Price,
            'isTakeOut' => $temp->IsTakeOut,
            'orderItems' => $items
        ]);
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        $input = $request->all();
        $input['orderID'] = $orderId;

        $validator = Validator::make($input, [
            'orderID' => 'required|exists:orders',
            'status' => 'required|integer|in:0,1,2,3,4,5,6,7,99',
            'estimatedTime' => 'date'
        ]);

        if ($validator->fails()) {
            return \response($validator->errors(), 422);
        }

        $order = Order::find($orderId);
        $order->update([
            'Status' => $request->input('status'),
            'UpdateTime' => Carbon::now(),
            'EstimatedTime' => $request->input('estimatedTime') ?? $order->EstimatedTime,
            'changed' => true
        ]);  

        return $this->sendOrderNotificationToFirebaseToCustomer($order->CustomerID, $order);
    }

    public function changedOrdersForCustomer($userId)
    {
        $changedOrders = Order::where('CustomerID', $userId)
            ->where('changed', true)
            ->get();

        $collection = collect();

        foreach ($changedOrders as $order) {
            $collection->push(
                collect([
                    'orderID' => $order->OrderID,
                    'orderNumber' => $order->OrderNumber,
                    'status' => $order->Status
                ])
            );

            $order->changed = false;
            $order->save();
        }

        return $collection;
    }

    public function newOrdersForStore($storeId)
    {
        $key = self::NEW_ORDER_CACHE_PREFIX . $storeId;
        $collection = Cache::pull($key) ?? collect();
        $newOrder = collect();

        foreach($collection as $order)
        {
            $newOrder->push(
                collect([
                    'orderID' => $order['OrderID'],
                    'orderNumber' => $order['OrderNumber'],
                    'status' => $order['Status']
                ])
            );
        }

        return $newOrder;
    }

    public function deleteOrder($orderId)
    {
        $order = Order::find($orderId);

        if ($order == null) {
            return \response('OrderID not found', 422);
        }

        $order->update([
            'Status' => 99,
            'changed' => true,
            'UpdateTime' => Carbon::now()
        ]);

        return $this->sendOrderNotificationToFirebaseToCustomer($order->CustomerID, $order);
    }

    public function removeOldOrder()
    {
        // $storeID = Cache::rememberForever('store', function () {
        //     return Store::all();
        // })->pluck('StoreID');

        // $key = '';
        // $newOrder = null;
        // $oldOders = null;

        // foreach($storeID as $id)
        // {
        //     $key = self::NEW_ORDER_CACHE_PREFIX . $id;
        //     $newOrder = Cache::pull($key) ?? collect();

        //     // store did not accept order for long time
        //     $oldOders = Order::where('StoreID', $id)
        //                 ->where('Status', 0)
        //                 ->where('DateTime', '<=', Carbon::now()->subHour(1));
        
        //     foreach($oldOders->get() as $order)
        //     {
        //         $newOrder->push(collect([
        //             'OrderID' => $order['OrderID'],
        //             'OrderNumber' => $order['OrderNumber'],
        //             'Status' => 5
        //         ]));
        //     }

        //     Cache::forever($key, $newOrder);

        //     $oldOders->update([
        //         'Status' => 5,
        //         'changed' => true,
        //         'UpdateTime' => Carbon::now()
        //     ]);
            
        //     foreach($oldOders as $order)
        //     {
        //         $this->sendOrderNotificationToFirebase(Store::find($id)->SuperUserID, $order);
        //         $this->sendOrderNotificationToFirebase($order->CustomerID, $order);
        //     }
            
        //     // customer did not get the meal for long time
        //     $notTakeOrders = Order::where('StoreID', $id)
        //                         ->where('Status', 2)
        //                         ->where('UpdateTime', '<=', Carbon::now()->subHour(1))
        //                         ->update([
        //                             'Status' => 4,
        //                             'changed' => true,
        //                             'UpdateTime' => Carbon::now()
        //     ]);

        //     foreach($notTakeOrders as $order)
        //     {
        //         $this->sendOrderNotificationToFirebase($order->CustomerID, $order);
        //     }
        // }   

        $oldOders = Order::where('Status', 0)
                        ->where('DateTime', '<=', Carbon::now()->subHour(1))
                        ->get();
                    
        Order::where('Status', 0)
                ->where('DateTime', '<=', Carbon::now()->subHour(1))
                ->update([
                    'Status' => 5,
                    'changed' => true,
                    'UpdateTime' => Carbon::now()
                ]);
        
        $oldOders->map(function ($item, $key) {
            $item->Status = 5;
            $item->changed = true;
            $item->UpdateTime = Carbon::now();
            return $item;
        });

        foreach($oldOders as $order)
        {
            $this->sendOrderNotificationToFirebaseToRestauranto($order->StoreID, $order);
            $this->sendOrderNotificationToFirebaseToCustomer($order->CustomerID, $order);
        }

        $notTakeOrders = Order::where('Status', 2)
                        ->where('UpdateTime', '<=', Carbon::now()->subHour(1))
                        ->get();
                        
        Order::where('Status', 2)
                ->where('UpdateTime', '<=', Carbon::now()->subHour(1))
                ->update([
                    'Status' => 4,
                    'changed' => true,
                    'UpdateTime' => Carbon::now()
                ]);

        $notTakeOrders->map(function ($item, $key) {
            $item->Status = 4;
            $item->changed = true;
            $item->UpdateTime = Carbon::now();
            return $item;
        });

        foreach($notTakeOrders as $order)
        {
            $this->sendOrderNotificationToFirebaseToCustomer($order->CustomerID, $order);
        }
    }

    public function sendOrderNotificationToFirebaseToRestaurant($storeId, $order)
    {
        $userId = Store::find($order->StoreID)->SuperUserID;
        $tokens = UserDeviceToken::where('UserID', $userId)->get()->pluck('DeviceToken');
        $dataString = null;
        $ch = null;
        $response = null;
        
        $headers = [
            'Authorization: key=AAAAZpv2HG0:APA91bHSSVVdMl12XSAR9QuQniLPLfHaVsocKQko89Zc82dIQg_-HNEkZxDNX0PFyrrmypIv3eqKN7MPR3JpXwwzAZoSW06XZQBiatB6FzXFkQtkKesdCpA1unenDXsIhxxUYV4dzd0_',
            'Content-Type: application/json',
        ];

        $data = [
            'to' => '',
            'notification' => [
                'title' => '訂單 #' . $order->OrderNumber,
                'body' => self::ORDER_STATUS_MESSAGE[$order->Status],  
            ]
        ];

        $collection = collect();

        foreach($tokens as $token)
        {
            $data['to'] = $token;
            
            $collection->push($data);
            $dataString = json_encode($data);
    
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
        } 
        
        return $response;
    }

    public function sendOrderNotificationToFirebaseToCustomer($userId, $order)
    {
        $tokens = UserDeviceToken::where('UserID', $userId)->get()->pluck('DeviceToken');
        $dataString = null;
        $ch = null;
        $response = null;
        
        $headers = [
            'Authorization: key=AAAAn80xNBI:APA91bHpS617rQut9kwzz0cpDCTvT9nPT0VlcIuap5mn_Fmr3NAN2r0bEDXcsPVkvz-XU00Ud-7HSnBOAXTOYY4JDTjwpnEGoD8Wfx2aZY7146TJ_W8Uy2R4tb2ybuL4SyX-iseFpRAi',
            'Content-Type: application/json',
        ];

        $data = [
            'to' => '',
            'notification' => [
                'title' => '訂單 #' . $order->OrderNumber,
                'body' => self::ORDER_STATUS_MESSAGE[$order->Status],  
            ]
        ];

        $collection = collect();

        foreach($tokens as $token)
        {
            $data['to'] = $token;
            
            $collection->push($data);
            $dataString = json_encode($data);
    
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
        } 
        
        return $response;
    }


    public function getAbleToDeliver()
    {
        //get all delivery orders

        $data = Order::where('IsTakeOut',1)
              ->where('IsDelivery',1)
              ->where('Status',6)
           //->join('orderitem', 'orderitem.OrderID', '=', 'orders.OrderID')
        //    ->join('store', 'store.StoreID', '=', 'orders.StoreID')
        //    ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
        //    ->leftJoin('orderitemflavor', 'orderitem.OrderItemID', '=', 'orderitemflavor.OrderItemID')
        //    ->leftJoin('flavor', 'orderitemflavor.FlavorID', '=', 'flavor.FlavorID')
        //    ->leftJoin('flavortype', 'flavortype.FlavorTypeID', '=', 'flavor.FlavorTypeID')
        //    ->select('*', 'orders.Memo', 'orderitem.OrderItemID', 'orders.DateTime as OrderDate')
        //    ->orderBy('orders.OrderID', 'desc')
            ->get();


           if ($data->isEmpty()) {
                return \response("", 204);
            }

    
            $collection = collect();
            $flavors = null;
            $flavorsPrice = 0;
            $temp = null;
            $count = 0;

            $orders = $data->groupBy('OrderID');

        foreach ($orders as $order) {
            $items = collect();
            $count = 0;

            $orderItems = $order->groupBy('OrderItemID');

            foreach ($orderItems as $orderItem) {
                if ($count >= 3) {
                    break;
                }

                $flavors = collect();
                $flavorsPrice = 0;
                
                foreach ($orderItem as $orderItemFlavor) {
                    if ($orderItemFlavor->FlavorTypeID == null) {
                        break;
                    }

                    $flavors->push(
                        collect([
                            'flavorType' => $orderItemFlavor->FlavorTypeName,
                            'flavor' => $orderItemFlavor->FlavorName,
                        ])
                    );

                    $flavorsPrice += $orderItemFlavor->ExtraPrice;
                }


                $temp = $orderItem[0];

                $items->push(
                    collect([
                        'id' => $temp->OrderItemID,
                        'name' => $temp->MealName,
                        'memo' => $temp->Memo,
                        'quantity' => $temp->Quantity,
                        'mealPrice' => $temp->MealPrice,
                        'flavors' => $flavors,
                        'amount' => $temp->MealPrice + $flavorsPrice,
                    ])
                );

                ++$count;
            }

            $collection->push(
                collect([
                    'id' => $temp->OrderID,
                    'orderNumber' => $temp->OrderNumber,
                    'store' => $temp->StoreName,
                    'status' => $temp->Status,
                    'orderDate' => $temp->OrderDate,
                    'estimatedTime' => $temp->EstimatedTime,
                    'orderMemo' => $temp->Memo,
                    'orderPrice' => $temp->Price,
                    'isTakeOut' => $temp->IsTakeOut,
                    'isDelivery' => $temp->IsDelivery,
                    'orderItems' => $items,
                    'serviceFee' => $temp->ServiceFee,
                    'destination' => $temp->Destination,
                    'totalAmount' => $temp->TotalAmount
                ])
            );
        }
           /* return collect([
                'id' => $temp->OrderID,
                 'orderNumber' => $temp->OrderNumber,
                 'store' => $temp->StoreName,
                 'status' => $temp->Status,
                 'orderDate' => $temp->OrderDate,
                 'estimatedTime' => $temp->EstimatedTime,
                 'orderMemo' => $temp->Memo,
                 'orderPrice' => $temp->Price,
                 'isTakeOut' => $temp->IsTakeOut,
                 'orderItems' => $items,
                 'isDelivery' => $temp->IsDelivery,
                 'destination' => $temp->Destination,
                 'totalAmount' => $temp->TotalAmount
             ]);*/

       return response()->json($collection);

    }
    
}
