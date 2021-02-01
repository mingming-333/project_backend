<?php

namespace App\Http\Controllers;

use Storage;
use App\User;
use App\Store;
use App\Foodcourt;
use App\UserDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordNotificationEmail;
use Carbon\Carbon;

class UserController extends Controller
{
    public function getUserInfo()
    {
        
    }

    public function editUserPassword(Request $request)
    {
        //validator input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return \response($validator->errors(), 422);
        }

        //try email and password are correct
        $data = array(
            'email' => $request->input('email'),
            'password' => $request->input('oldPassword')
        );

        if (!auth()->attempt($data)) {
            return response(['message' => 'Invalid credentials'], 400);
        }

        //update password
        auth()->user()->update([
            'password' => Hash::make($request->input('newPassword'))
        ]);

        //send password reset notification
        Mail::to(auth()->user()->email)
            ->send(new ResetPasswordNotificationEmail);
    }

    public function updateUserInfo(Request $request, $userId)
    {
        //validator input
        $validator = Validator::make($request->all(), [
            'userName' => 'string'
        ]);

        if ($validator->fails()) {
            return \response($validator->errors(), 422);
        }

        $user = User::find($userId);

        if ($user == null) {
            return \response('UserID not found', 422);
        }

        //image proccess
        $new_filename = $user->UserAvatarPath;

        if ($request->has('userAvatar')) {
            $new_filename = date('YmdHis') . '_' . $user->id . '_' . $user->name . '.' . 'png';
            $image = $request->userAvatar;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            Storage::put('public/avatar/' . $new_filename, base64_decode($image));
            $old_file_name = $user->UserAvatarPath;

            if ($old_file_name != 'preset.png') {
                Storage::delete('public/avatar/' . $old_file_name);
            }
        }

        //update user info
        $user->update([
            'name' => $request->input('userName'),
            'UserAvatarPath' => $new_filename
        ]);

        return collect([
            'imagePath' => $new_filename
        ]);
    }

    public function addDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userID' => 'required|exists:users,id',
            'token' => 'required|string'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        $userDeviceTokens = UserDeviceToken::where('DeviceToken', $request->input('token'))->get()->first();

        if($userDeviceTokens == null)
        {
            UserDeviceToken::create([
                'UserID' => $request->input('userID'),
                'DeviceToken' => $request->input('token'),
                'LatestTime' => Carbon::now()
            ]);
        }     
    }

    public function deleteDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userID' => 'required|exists:users,id',
            'token' => 'required|string'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 422);
        }

        UserDeviceToken::where('DeviceToken', $request->input('token'))->delete();
    }
}
