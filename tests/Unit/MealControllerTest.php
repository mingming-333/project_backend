<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\MealController;

class MealControllerTest extends TestCase
{
     /**@test */
    public function test_getAllMeals()
    {
        $mealController = new MealController();
        
        $data = $mealController->getAllMeals(1);
        $this->assertTrue($data->count() > 0);
        $this->assertTrue($data[3]['items']->where('id', 25)->isEmpty()); //del_flag = 1

        $data = $mealController->getAllMeals(99);
        $this->assertTrue($data->isEmpty());
    }

     /**@test */
     public function test_getStoreMealList()
     {
         $mealController = new MealController();
         
         $data = $mealController->getStoreMealList(1);
         $this->assertTrue($data->count() > 0);
         $this->assertTrue($data['storeID'] == 1);
         $this->assertTrue($data['menu'][0]['mealType'] == '今日特餐');
 
         $data = $mealController->getStoreMealList(99);
         $this->assertTrue($data->isEmpty());
     }

     /**@test */
    public function test_getMeal()
    {
        $mealController = new MealController();
        
        $data = $mealController->getMeal(1, 1);
        $this->assertTrue($data['foodName'] == '乾拌麵');
        $this->assertTrue($data['foodPrice'] == 40);

        $data = $mealController->getMeal(1, 10);
        $this->assertTrue($data['foodName'] == '貢丸麵');
        $this->assertTrue($data['foodPrice'] == 40);

        $data = $mealController->getMeal(1, 25); //del_flag = 1
        $this->assertTrue($data->isEmpty());

        $data = $mealController->getMeal(1, 99);
        $this->assertTrue($data->isEmpty());

        $data = $mealController->getMeal(99, 10);
        $this->assertTrue($data->isEmpty());
    }

    /**@test */
    public function test_getTodaySpecial()
    {
        $mealController = new MealController();
        
        $data = $mealController->getTodaySpecial();
        $this->assertTrue($data['store'][0]['storeID'] == 1);
        $this->assertTrue($data['store'][0]['mealList']->where('mealID', 7)->count() > 0);
    }
}
