<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Order;
use App\OrderItem;
use App\Meal;
use App\Store;

class ReportController extends Controller
{
    public function showReport($storeId)
    {
        if(!Store::where('storeID', $storeId)->exists())
        {
            return \response('StoreID not found', 422);
        }

        $current = Carbon::now();

        $dayRevenue = Order::where('StoreID', $storeId)
                            ->where('Status', 3)
                            ->whereDate('DateTime', $current)
                            ->sum('Price');

        $monthRevenue = Order::where('StoreID', $storeId)
                                ->where('Status', 3)
                                ->whereYear('DateTime', $current->year)
                                ->whereMonth('DateTime', $current->month)
                                ->sum('Price');

        $mealRanking = OrderItem::join('orders', 'orderItem.OrderID', '=', 'orders.OrderID')
                                    ->join('meal', 'meal.MealID', '=', 'orderitem.MealID')
                                    ->where('StoreID', $storeId)
                                    ->where('Status', 3)
                                    ->whereYear('orders.DateTime', $current->year)
                                    ->whereMonth('orders.DateTime', $current->month)
                                    ->selectRaw('MealName, SUM(Amount) AS sum, SUM(Quantity) AS count')
                                    ->groupBy('meal.MealID')
                                    ->orderBy('count', 'desc')
                                    ->take(10)
                                    ->get();

        $ranking = collect();
        $rank = 1;

        foreach($mealRanking as $data)
        {
            $array = array(
                $rank,
                $data->MealName,
                (int)$data->count,
                (int)$data->sum
            );

            $ranking->push($array);
            $rank++;
        }

        $historyHourRevenues = Order::selectRaw('SUM(Price) as revenue, DATE_FORMAT(DateTime, "%H:00") as hour')
                                    ->where('StoreID', $storeId)
                                    ->where('Status', 3)
                                    ->where('DateTime', '>', $current->copy()->subHours(12)->toDateTimeString())
                                    ->groupBy('hour')
                                    ->get();

        $hourRevenues = collect();
        
        for($i = 12; $i > 0; $i--)
        {  
            $hour = $current->copy()->subHours($i)->hour . ":00";
            $temp = $historyHourRevenues->where('hour', $hour)->first();

            if($temp == null)
            {
                $hourRevenues->push(collect([
                    'revenue' => 0,
                    'hour' => $hour
                ]));
            }
            else
            {
                $hourRevenues->push(collect([
                    'revenue' => (int)$temp->revenue,
                    'hour' => $hour
                ]));
            }
        }

        $historyWeekRevenues = Order::selectRaw('SUM(Price) as revenue, DATE_FORMAT(DateTime, "%m/%d") as date')
                                    ->where('StoreID', $storeId)
                                    ->where('Status', 3)
                                    ->where('DateTime', '>', $current->copy()->subDays(7)->toDateTimeString())
                                    ->groupBy('date')
                                    ->get();

        $weekRevenues = collect();

        for($i = 7; $i > 0; $i--)
        {
            $date = $current->copy()->subDays($i)->format('m/d');
            $temp = $historyWeekRevenues->where('date', $date)->first();

            if($temp == null)
            {
                $weekRevenues->push(collect([
                    'revenue' => 0,
                    'date' => $date
                ]));
            }
            else
            {
                $weekRevenues->push(collect([
                    'revenue' => (int)$temp->revenue,
                    'date' => $date
                ]));
            }
        }

        $historyMonthRevenues = Order::selectRaw('SUM(Price) as revenue, DATE_FORMAT(DateTime, "%m/%d") as date')
                                    ->where('StoreID', $storeId)
                                    ->where('Status', 3)
                                    ->where('DateTime', '>', $current->copy()->subMonths(1)->toDateTimeString())
                                    ->groupBy('date')
                                    ->get();                                   
        
        $monthRevenues = collect();

        for($i = 30; $i > 0; $i--)
        {
            $date = $current->copy()->subDays($i)->format('m/d');
            $temp = $historyMonthRevenues->where('date', $date)->first();

            if($temp == null)
            {
                $monthRevenues->push(collect([
                    'revenue' => 0,
                    'date' => $date
                ]));
            }
            else
            {
                $monthRevenues->push(collect([
                    'revenue' => (int)$temp->revenue,
                    'date' => $date
                ]));
            }
        }

        $collection = collect([
            'dayRevenue' => $dayRevenue,
            'monthRevenue' => $monthRevenue,
            'mealRanking' => $ranking,
            'historyHourRevenue' => collect([
                'date' => Arr::pluck($hourRevenues, 'hour'),
                'revenue' => Arr::pluck($hourRevenues, 'revenue')
            ]),
            'historyWeekRevenue' => collect([
                'date' => Arr::pluck($weekRevenues, 'date'),
                'revenue' => Arr::pluck($weekRevenues, 'revenue')
            ]),
            'historyDayRevenue' => collect([
                'date' => Arr::pluck($monthRevenues, 'date'),
                'revenue' => Arr::pluck($monthRevenues, 'revenue')
            ])
        ]);

        return $collection;       
    }
}
