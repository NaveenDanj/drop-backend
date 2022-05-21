<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserResetPasswordController extends Controller
{

    public function forgotPassword(Request $request){

        $request->validate(['email' => 'required|email']);

        $user = User::where('email' , $request->email)->first();

        if($user == null){
            return response()->json([
                'message' => 'User not found'
            ] , 404);
        }


        $status = Password::sendResetLink(
            $request->only('email')
        );


        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Reset password link sent to your email',
                'status' => __($status)
            ] , 200);
        }else{

            return response()->json([
                'message' => 'Email not found'
            ] , 404);

        }



    }

    public function resetPassword(Request $request){

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);

        $status = Password::reset(
            $request->only('email' , 'password' , 'password_confirmation' , 'token'),
            function($user) use ($request){
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => null,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reseted successfully',
                'status' => __($status)
            ] , 200);
        }else{

            return response()->json([
                'message' => __($status)
            ] , 500);

        }


    }


}
