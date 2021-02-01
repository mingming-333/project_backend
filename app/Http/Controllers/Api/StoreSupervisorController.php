<?php

namespace App\Http\Controllers\Api;

use App\Store;
use App\User;
use App\Mail\StoreSupervisorInvitationEmail;
use App\StoreSupervisorInvitation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class StoreSupervisorController extends Controller
{
    public function addStoreSupervisorInvitation(Request $request)
    {
        return \response('This feature is not release.', 404);

        $validator = Validator::make($request->all(), [
            'storeId' => 'required|exists:store',
            'email' => 'required|email|regex:/@(mail.)?ntust.edu.tw/'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $token = Hash::make(str_random());

        StoreSupervisorInvitation::create([
            'StoreID' => $request->input('storeId'),
            'Email' => $request->input('email'),
            'Token' => $token
        ]);

        Mail::to($request->input('email'))
            ->send(new StoreSupervisorInvitationEmail($token));
    }

    public function storeSupervisorRegister(Request $request)
    {
        return view('storeSupervisor.register')->with('token', $request->input('token'));
    }

    public function storeSupervisorLogin(Request $request)
    {
        return view('storeSupervisor.login')->with('token', $request->input('token'));
    }

    public function addStoreSupervisorRegisterAccept(Request $request)
    {
        $invite = StoreSupervisorInvitation::where('token', $request->input('token'))->get()->first();

        if(!$invite)
        {
            return \response('Error invitation', 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|regex:/(0)[0-9]{9}/',
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $user =  User::create([
            'name' => $request->input('name'),
            'email' =>$request->input('email'),
            'password' => Hash::make($request->input('password')),
            'phone' => $request->input('phone'),
            'role' => 2
        ]);

        event(new Registered($user));

        $store = Store::find($invite->StoreID);
        $store->SuperUserID = $user->id;
        $store->save();

        $invite->delete();

        return 'You are successfully register';
    }

    public function addStoreSupervisorLoginAccept(Request $request)
    {
        $invite = StoreSupervisorInvitation::where('token', $request->input('token'))->get()->first();

        if(!$invite)
        {
            return \response('Error invitation', 422);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        if (!auth()->attempt($request->only(['email', 'password'])))
        {
            return response(['message' => 'Invalid credentials']);
        }

        $store = Store::find($invite->StoreID);
        $store->SuperUserID = auth()->user()->id;
        $store->save();

        $user = User::find(auth()->user()->id);
        $user->role = 2;
        $user->save();

        $invite->delete();

        return 'You are successfully login';
    }
}
