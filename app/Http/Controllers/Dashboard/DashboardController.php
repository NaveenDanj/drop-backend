<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserFile;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function userFiles(Request $request , $userid){

        //get all user files and paginate
        $userFiles = UserFile::where('user_id' , $userid)->paginate(10);

        // get logged in user
        $user = auth()->user();

        if($user->id != $userid){
            return response()->json([
                'error' => 'Unauthorized'
            ] , 401);
        }

        return response()->json([
            'userFiles' => $userFiles,
        ]);

    }

    public function getUserFileCount(Request $request){

        // get the user file count
        $userFileCount = UserFile::where('user_id' , $request->user()->id)->count();

        return response()->json([
            'success' => true,
            'userFiles' => $userFileCount,
        ]);

    }

}
