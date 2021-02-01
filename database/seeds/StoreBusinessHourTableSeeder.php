<?php

use App\StoreBusinessHour;
use Illuminate\Database\Seeder;

class StoreBusinessHourTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // StoreBusinessHour::create([
        //     'StoreID' => '1',
        //     'BusinessHour' => '6:30',
        //     'StoreState' => 1
        // ]);

        // StoreBusinessHour::create([
        //     'StoreID' => '1',
        //     'BusinessHour' => '11:30',
        //     'StoreState' => 0
        // ]);

        // StoreBusinessHour::create([
        //     'StoreID' => '1',
        //     'BusinessHour' => '13:30',
        //     'StoreState' => 1
        // ]);

        // StoreBusinessHour::create([
        //     'StoreID' => '1',
        //     'BusinessHour' => '19:30',
        //     'StoreState' => 0
        // ]);

        StoreBusinessHour::create([
            'StoreID' => 2,
            'BusinessHour' => '8:30',
            'StoreState' => 1
        ]);

        StoreBusinessHour::create([
            'StoreID' => 2,
            'BusinessHour' => '19:00',
            'StoreState' => 0
        ]);

        StoreBusinessHour::create([
            'StoreID' => 3,
            'BusinessHour' => '9:00',
            'StoreState' => 1
        ]);

        StoreBusinessHour::create([
            'StoreID' => 3,
            'BusinessHour' => '11:30',
            'StoreState' => 0
        ]);

        StoreBusinessHour::create([
            'StoreID' => 3,
            'BusinessHour' => '15:00',
            'StoreState' => 1
        ]);

        StoreBusinessHour::create([
            'StoreID' => 3,
            'BusinessHour' => '19:00',
            'StoreState' => 0
        ]);
    }
}
