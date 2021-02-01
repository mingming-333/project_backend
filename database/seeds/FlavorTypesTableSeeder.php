<?php

use App\FlavorType;
use Illuminate\Database\Seeder;

class FlavorTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    //    FlavorType::create([ //1
    //        'FlavorTypeName' => '鍋燒麵肉種類',
    //        'StoreID' => 1,
    //        'isRequired' => 1,
    //        'isMultiple' => 0
    //     ]);

    //    FlavorType::create([ //2
    //     'FlavorTypeName' => '炒泡麵肉種類',
    //     'StoreID' => 1,
    //     'isRequired' => 1,
    //     'isMultiple' => 0
    //     ]);

        FlavorType::create([ //3
            'FlavorTypeName' => '冰塊',
            'StoreID' => 2,
            'isRequired' => 1,
            'isMultiple' => 0
        ]);

        FlavorType::create([ //4
            'FlavorTypeName' => '甜度',
            'StoreID' => 2,
            'isRequired' => 1,
            'isMultiple' => 0
        ]);

        FlavorType::create([ //5
            'FlavorTypeName' => '加料',
            'StoreID' => 3,
            'isRequired' => 0,
            'isMultiple' => 1
        ]);

        FlavorType::create([ //6
            'FlavorTypeName' => '冰塊',
            'StoreID' => 3,
            'isRequired' => 1,
            'isMultiple' => 0
        ]);

        FlavorType::create([ //7
            'FlavorTypeName' => '甜度',
            'StoreID' => 3,
            'isRequired' => 1,
            'isMultiple' => 0
        ]);

        FlavorType::create([ //8
            'FlavorTypeName' => '加價購',
            'StoreID' => 3,
            'isRequired' => 0,
            'isMultiple' => 1
        ]);

        FlavorType::create([ //9
            'FlavorTypeName' => '冰/熱',
            'StoreID' => 2,
            'isRequired' => 1,
            'isMultiple' => 0
        ]);

    }
}
