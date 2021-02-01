<?php

use App\Foodcourt;
use Illuminate\Database\Seeder;

class FoodcourtsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // Foodcourt::create([
        //     'FoodCourtName' => '台灣科技大學 第三學生餐廳',
        //     'FoodCourtDescription' => '台灣科技大學 第三學生餐廳',
        //     'SuperUserID' => 1,
        // ]);

        // Foodcourt::create([
        //     'FoodCourtName' => '台灣科技大學 小木屋',
        //     'FoodCourtDescription' => '台灣科技大學 小木屋',
        //     'SuperUserID' => 1,
        // ]);

        Foodcourt::create([
            'FoodCourtName' => '台灣科技大學 後餐',
            'FoodCourtDescription' => '台灣科技大學 後餐',
            'SuperUserID' => 1,
        ]);
    }
}
