<?php

use App\MenuType;
use Illuminate\Database\Seeder;

class MenuTypesTableSeeder extends Seeder
{
    public function run()
    {
        //豪享來
        // MenuType::create([  //1
        //     'MenuTypeName' => "乾麵",
        //     'StoreID' => 1,
        // ]);
        // MenuType::create([  //2
        //     'MenuTypeName' => "湯麵",
        //     'StoreID' => 1,
        // ]);
        // MenuType::create([  //3
        //     'MenuTypeName' => "簡餐",
        //     'StoreID' => 1,
        // ]);
        // MenuType::create([  //4
        //     'MenuTypeName' => "湯類",
        //     'StoreID' => 1,
        // ]);

        //赤糰日式米食
        MenuType::create([ //5
            'MenuTypeName' => "日式飯糰",
            'StoreID' => 2,
        ]);

        MenuType::create([ //6
            'MenuTypeName' => "丼飯",
            'StoreID' => 2,
        ]);

        MenuType::create([ //7
            'MenuTypeName' => "鬆餅",
            'StoreID' => 2,
        ]);

        MenuType::create([ //8
            'MenuTypeName' => "飲料",
            'StoreID' => 2,
        ]);

        //穀倉
        MenuType::create([ //9
            'MenuTypeName' => "醇飲M/L",
            'StoreID' => 3,
        ]);

        MenuType::create([ //10
            'MenuTypeName' => "醇奶L",
            'StoreID' => 3,
        ]);

        MenuType::create([ //11
            'MenuTypeName' => "咖啡M",
            'StoreID' => 3,
        ]);

        MenuType::create([ //12
            'MenuTypeName' => "炸物",
            'StoreID' => 3,
        ]);

        MenuType::create([ //13
            'MenuTypeName' => "熱壓麵包",
            'StoreID' => 3,
        ]);

        MenuType::create([ //14
            'MenuTypeName' => "吃飽飽",
            'StoreID' => 3,
        ]);
    }
}
