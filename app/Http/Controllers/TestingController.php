<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Testing;

$collection = collect();

class TestingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Testing::all();
        //較不會有問題，因為可以
    
        return response()->json($data);
        //↑↑通常這樣傳就可以了
        //return response('',200)->json($datas,200);    
        //把我們需要的資料轉成json傳給前端
        //json：一個key值對一個value
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)  //request是前端傳來的資料
    {
        //要先取得他們傳來的資料，用all()可取得他們傳來的所有data
        $data = $request->all();

        //if只想取得特定資料，ex：name
        $name = $request->input('name');
        //or
        $name = $request->name;

        if($name == null) //表示前端沒有傳資料過來
        {
            return response('',400);
        }

        //SO 如果和前端有很好的串接的話，CREATE裡面應該只會有這樣
       $test = Testing::create($data);

        //寫死
        /*Testing::create([
            'name' =>'abc',
            'number' => '123456'
        ]);*/

        //HTTP code
        return response($test,201); //201：我接受你傳過來的資料且我創立成功


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $testing = Testing::find($id);
        /*->select('name','number');*/
        //select 是指我只要傳某幾個資料，像這邊就是我只要傳name和number

        $collection = collect([
            'name'=>$testing->name,
            'number'=>$testing->number
        ]);
        

        //需先判斷資料有沒有問題
        if($testing)
            return response($collection);
        else
            return response('',404);
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        //ex 1
        Testing::find($id)->update($data);

        /*Testing::find($id)->update([

            'name' => 'abc',
            'number' => '123456'
        ]);*/
        
        //ex 2 
        /*$date = Testing::find($id);
        $data->name='123';
        $data->save(); */

        return response($data,201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $data = $request->all();
        Testing::delete($data);
        //
    }
}
