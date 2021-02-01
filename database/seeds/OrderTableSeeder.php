<?php

use App\Order;
use App\Meal;
use App\FlavorType;
use App\Flavor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Order::class, 10)->create()
        ->each(function ($order) {

            $orderItems = factory(App\OrderItem::class, rand($min = 1, $max = 5))
            ->create([
                'OrderID' => $order->OrderID
            ])
            ->each(function ($orderItem) {

                $extraPrice = 0;
                
                $flavorTypes = Meal::where('meal.MealID', $orderItem->MealID)
                                    ->join('mealflavor', 'mealflavor.MealID', '=', 'meal.MealID')
                                    ->join('flavortype', 'mealflavor.FlavorTypeID', '=', 'flavortype.FlavorTypeID')
                                    ->get();
                
                foreach($flavorTypes as $flavorType)
                {   
                    $minimum = $flavorType->isRequired;
                    $maxinum = $flavorType->isMultiple ? Flavor::where('FlavorTypeID', $flavorType->FlavorTypeID)->count() : 1;

                    $num = rand($min = $minimum, $max = $maxinum);

                    $orderItemFlavors = factory(App\OrderItemFlavor::class, $num)
                    ->make([
                        'OrderItemID' => $orderItem->OrderItemID,
                    ]);

                    foreach($orderItemFlavors as $orderItemFlavor)
                    {
                        $flavor = Flavor::where('FlavorTypeID', $flavorType->FlavorTypeID)
                                        ->get()
                                        ->random();

                        $orderItemFlavor->FlavorID = $flavor->FlavorID;
                        $extraPrice += $flavor->ExtraPrice;
                        $orderItemFlavor->save();
                    }
                }  
                
                $orderItem->Amount += $extraPrice * $orderItem->Quantity; 
                $orderItem->save();
            });

            $order->IsTakeOut =  rand(0,1) == 1;
            $order->Price = $orderItems->sum('Amount');
            $order->DateTime = Carbon::now();
            $order->UpdateTime = Carbon::now();
            $order->save();
        });
    }
}
