<?php

use App\MealFlavor;
use Illuminate\Database\Seeder;

class MealFlavorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // MealFlavor::create([
        //     'MealID' => 7,
        //     'FlavorTypeID' => 1
        // ]);

        // MealFlavor::create([
        //     'MealID' => 6,
        //     'FlavorTypeID' => 2
        // ]);

        //赤糰日式米食

        for($i = 78; $i <= 91; $i++)
        {
            MealFlavor::create([
                'MealID' => $i,
                'FlavorTypeID' => 9
            ]);

            MealFlavor::create([
                'MealID' => $i,
                'FlavorTypeID' => 10
            ]);

            if($i != 72 && $i != 74 && $i != 76)
            {
                MealFlavor::create([
                    'MealID' => $i,
                    'FlavorTypeID' => 15
                ]);
            }
        }

        for($i = 92; $i <= 116; $i++)
        {
            MealFlavor::create([
                'MealID' => $i,
                'FlavorTypeID' => 11
            ]);

            MealFlavor::create([
                'MealID' => $i,
                'FlavorTypeID' => 12
            ]);

            MealFlavor::create([
                'MealID' => $i,
                'FlavorTypeID' => 13
            ]);
        
        }

        for($i = 122; $i <= 139; $i++)
        {
            MealFlavor::create([
                'MealID' => $i,
                'FlavorTypeID' => 14
            ]);
        }
    }
}
