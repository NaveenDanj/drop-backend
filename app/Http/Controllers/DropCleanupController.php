<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DropCleanupController extends Controller
{

    public function DropCleanupClean(){

        // get all the files
        $user_files = UserFile::all();

        // loop through the files
        foreach ($user_files as $user_file) {

            // get today timestamp in miliseconds
            $today_time = time() * 1000;

            // get the file created datetinm and convert it into miliseconds
            $file_created_time = strtotime($user_file->created_at) * 1000;
            // get the expired timestamp and add it
            $expired_time = $file_created_time + $user_file->expired_time;

            // if the file is expired
            if ($expired_time < $today_time) {
                // delete the file
                if(Storage::disk('local')->exists("files/{$user_file->name}")){
                    Storage::disk('local')->delete("files/{$user_file->name}");
                }
            }

        }

        return response()->json([
            'message' => 'Cleanup completed'
        ] , 200);


    }

}
