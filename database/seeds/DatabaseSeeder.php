<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(UsersTableSeeder::class);
        //$this->call(FoodcourtsTableSeeder::class);
        //$this->call(StoresTableSeeder::class);
        //$this->call(MenuTypesTableSeeder::class);
        //$this->call(FlavorTypesTableSeeder::class);
		//$this->call(FlavorsTableSeeder::class);
        //$this->call(MealsTableSeeder::class);
        $this->call(MealFlavorsTableSeeder::class);
        // $this->call(OrderTableSeeder::class);
        //$this->call(StoreBusinessDayTableSeeder::class);
        //$this->call(StoreBusinessHourTableSeeder::class);
    }
}
