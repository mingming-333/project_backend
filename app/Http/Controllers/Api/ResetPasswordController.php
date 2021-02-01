<?php

namespace App\Http\Controllers\Api;

use App\Mail\ResetPasswordNotificationEmail;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected function sendResetResponse(Request $request, $response)
    {
        Mail::to($request->input('email'))
            ->send(new ResetPasswordNotificationEmail);

        return view('auth.message.success_message',['message'=> trans($response)]);

    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return view('auth.message.warning_message',['message'=> trans($response)]);
    }


}
