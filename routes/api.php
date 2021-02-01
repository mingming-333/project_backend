<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//User

Route::post('/register', 'Api\AuthController@register');

Route::post('/login', 'Api\AuthController@login');

Route::post('/logout', 'Api\AuthController@logout')->middleware('auth.jwt');

Route::post('/password/email', 'Api\ForgotPasswordController@sendResetLinkEmail');

Route::get('/email/resend', 'Api\VerificationController@resend')->name('verification.resend');

Route::get('/email/verify', 'Api\VerificationController@verify_f')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', 'Api\VerificationController@verify')->name('verification.verify');

Route::post('/user/logout', 'Auth\LoginController@logout');

Route::post('/password/edit', 'UserController@editUserPassword')->middleware('laravel.jwt');

Route::put('/{userId}/userInfo', 'UserController@updateUserInfo')->middleware('laravel.jwt');

Route::post('/deviceToken', 'UserController@addDeviceToken')->middleware('laravel.jwt');

Route::delete('/deviceToken', 'UserController@deleteDeviceToken')->middleware('laravel.jwt');

//Store

Route::post('/store/add', "StoreController@addStore")->middleware('laravel.jwt');

//Route::delete('/store/{storeId}/delete', "StoreController@deleteStore")->middleware('laravel.jwt');

Route::post('/store/addSupervisor', 'Api\StoreSupervisorController@addStoreSupervisorInvitation')->middleware('laravel.jwt');;

Route::get('/{storeId}/storeInfo', 'StoreController@getStoreInfo');

Route::put('/{storeId}/storeInfo', 'StoreController@updateStoreInfo')->middleware('laravel.jwt');

Route::post('/{storeId}/storeStatus', 'StoreController@updateStoreStatus')->middleware('laravel.jwt');

Route::get('/store/state/update', 'StoreController@changeStoreStateBySchedule');

//Meal

Route::get('/{storeId}/meals', 'MealController@getAllMeals');

Route::get('/customer/{storeId}/meals', 'MealController@getStoreMealList');

Route::get('/{storeId}/meals/{mealId}', 'MealController@getMeal');

Route::post('/{storeId}/meals/', 'MealController@storeMeal')->middleware('laravel.jwt');

Route::put('/{storeId}/meals/{mealId}', 'MealController@updateMeal')->middleware('laravel.jwt');

Route::delete('/{storeId}/meals/{mealId}', 'MealController@deleteMeal')->middleware('laravel.jwt');

Route::put('/{storeId}/meals/{mealId}/menuType', 'MealController@changeMenuType')->middleware('laravel.jwt');

Route::put('/{storeId}/meals/{mealId}/mealSoldOut', 'MealController@changeMealStatus')->middleware('laravel.jwt');

Route::post('/meals/search', 'MealController@searchMeal');

Route::get('/todaySpecial', 'MealController@getTodaySpecial');

//Menu Type

Route::get('/{storeId}/menuType', 'MenuTypeController@getAllMenuType');

Route::post('/{storeId}/menuType', 'MenuTypeController@storeMenuType')->middleware('laravel.jwt');

Route::put('/{storeId}/menuType','MenuTypeController@updateMenuType')->middleware('laravel.jwt');

Route::delete('/{storeId}/menuType/{menuTypeId}', 'MenuTypeController@deleteMenuType')->middleware('laravel.jwt');

//Flavor

Route::get('/{storeId}/meals/{mealId}/flavors', 'FlavorController@getFlavors');

Route::put('/{storeId}/meals/{mealId}/flavors/{flavorTypeId}', 'FlavorController@updateFlavors')->middleware('laravel.jwt');

//Flavor Type

Route::post('/{storeId}/meals/{mealId}/flavors', 'FlavorTypeController@storeFlavorType')->middleware('laravel.jwt');

Route::put('/{storeId}/meals/{mealId}/flavorTypes/{flavorTypeId}', 'FlavorTypeController@updateFlavorType')->middleware('laravel.jwt');

Route::delete('/{storeId}/meals/{mealId}/flavors/{flavorTypeId}', 'FlavorTypeController@deleteFlavorType')->middleware('laravel.jwt');

//Cart

Route::get('/{userId}/cart', 'CartController@getCartItem')->middleware('laravel.jwt');

Route::post('/{userId}/cart', 'CartController@storeCartItem')->middleware('laravel.jwt');

Route::delete('/{userId}/cart/{cartItemId}', 'CartController@deleteCartItem')->middleware('laravel.jwt');

Route::put('/{userId}/cart/{cartItemId}', 'CartController@updateCartItem')->middleware('laravel.jwt');

Route::delete('/{userId}/cart', 'CartController@deleteAllCartItem')->middleware('laravel.jwt');

//Order

Route::post('/{userId}/orderFromCart', 'OrderController@storeOrderFromCart')->middleware('laravel.jwt');

Route::get('/store/{storeId}/currentOrders', 'OrderController@getStoreCurrentOrders')->middleware('laravel.jwt');

Route::get('/store/{storeId}/historyOrders', 'OrderController@getStoreHistoryOrders')->middleware('laravel.jwt');

Route::get('/customer/{customerId}/currentOrders', 'OrderController@getCustomerCurrentOrders')->middleware('laravel.jwt');

Route::get('/customer/{customerId}/historyOrders', 'OrderController@getCustomerHistoryOrders')->middleware('laravel.jwt');

Route::get('/store/{orderId}/orders/detail', 'OrderController@getStoreOrderDetail')->middleware('laravel.jwt');

Route::get('/customer/{orderId}/orders/detail', 'OrderController@getCustomerOrderDetail')->middleware('laravel.jwt');

Route::put('/store/{orderId}/orders/status', 'OrderController@updateOrderStatus')->middleware('laravel.jwt');

Route::delete('/store/{orderId}/orders/delete', 'OrderController@deleteOrder')->middleware('laravel.jwt');

//Route::get('/customer/{userId}/updatedOrders', 'OrderController@changedOrdersForCustomer')->middleware('laravel.jwt');

//Route::get('/store/{storeId}/updatedOrders', 'OrderController@newOrdersForStore')->middleware('laravel.jwt');

Route::get('/store/{storeId}/orders/refresh', 'OrderController@removeOldOrder');

Route::get('/orders','OrderController@getAbleToDeliver');

//Report

Route::get('/{storeId}/analytics', 'ReportController@showReport')->middleware('laravel.jwt');

//MainPage

Route::get('/main', 'MainPageController@getMainPageData');

Route::get('/choosingGame/store', 'MainPageController@choosingGameForStore');

Route::get('/choosingGame/meal', 'MainPageController@choosingGameForMeal');

//Config

Route::get('/customer/versionNumber', 'ConfigController@getCustomerVersion');

Route::get('/restaurant/versionNumber', 'ConfigController@getRestaurantVersion');

Route::get('/privacyPolicy', 'ConfigController@getPrivacyPolicy');

//testing
//Controller@function名稱
Route::get('/testings', 'TestingController@index');  //user打api可取得我們的資料

Route::get('/testings/{id}', 'TestingController@show');

Route::post('/testings', 'TestingController@store'); //user丟資訊給我們，我們做接收(ex新增)

Route::put('/testings/{id}', 'TestingController@update');  //丟資訊給我們，我們做修改(修改user資訊)

Route::delete('/testings/{id}', 'TestingController@delete'); //刪掉

//更新玩route後要打指令：php artisan route:cache
//來把route更新，不然就會一直用舊的

//Delivery
