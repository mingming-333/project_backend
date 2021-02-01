<?php

use App\Store;
use Illuminate\Database\Seeder;

class StoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // Store::create([
        //     'StoreName' => '豪享來 精緻麵食館',
        //     'StoreDescription' => '豪享來 精緻麵食館',
        //     'FoodCourtID' => 1,
        //     'SuperUserID' => 1,
        // ]);

        // Store::create([
        //     'StoreName' => '赤糰日式米食',
        //     'StoreDescription' => '',
        //     'FoodCourtID' => 2,
        //     'SuperUserID' => 1,
        // ]);

        // Store::create([
        //     'StoreName' => '穀倉',
        //     'StoreDescription' => '',
        //     'FoodCourtID' => 2,
        //     'SuperUserID' => 1,
        // ]);

        Store::create([
            'StoreName' => '臭豆腐',
            'StoreDescription' => '',
            'FoodCourtID' => 3,
            'SuperUserID' => 1,
        ]);

        Store::create([
            'StoreName' => '鹹酥雞',
            'StoreDescription' => '',
            'FoodCourtID' => 3,
            'SuperUserID' => 1,
        ]);
    }
}
