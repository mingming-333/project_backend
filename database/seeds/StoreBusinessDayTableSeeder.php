<?php

use Illuminate\Database\Seeder;
use App\StoreBusinessDay;

class StoreBusinessDayTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // for($i = 1; $i < 5; $i++)
        // {
        //     StoreBusinessDay::create([
        //         'BusinessDay' => $i,
        //         'StoreID' => 1,
        //     ]);
        // }

        for($i = 1; $i < 6; $i++)
        {
            StoreBusinessDay::create([
                'BusinessDay' => $i,
                'StoreID' => 2,
            ]);
        }

        for($i = 1; $i < 5; $i++)
        {
            StoreBusinessDay::create([
                'BusinessDay' => $i,
                'StoreID' => 3,
            ]);
        }
    }
}
