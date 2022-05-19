<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{

    public function login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email' , $request->email)->first();

        if($user == null){
            return response()->json([
                'message' => 'User not found'
            ] , 404);
        }

        if (! Hash::check($request->password, $user->password)) {

            return response()->json([
                'message' => 'Email or password is incorrect'
            ] , 401);

        }

        $token = $user->createToken('default')->plainTextToken;
        // return the token
        return response()->json([
            'token' => $token,
            'user' => $user
        ] , 200);

    }

    public function register(Request $request){

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('default')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ] , 200);

    }


}
