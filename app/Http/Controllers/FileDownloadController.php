<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class FileDownloadController extends Controller
{

    public function downloadFile(Request $request , $linkid , $token){
    	// $myFile = storage_path("app/public/remember the name_1632552046.mp4");

        $requested_file = UserFile::where('fileID' , $linkid)->first();
        $myFile =  storage_path('app/files/'.$requested_file->name);

        // decrypt the token and check if it's valid
        $decrypted_token = Crypt::decrypt($token);

        return response()->download($myFile);


    }



    public function checkFileExists(Request $request , $linkid){

        $requested_file = UserFile::where('fileID' , $linkid)->first();

        if ($requested_file === null) {

            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'File not found'
                ] , 404
            );

        }else{

            // check if download limit is reached
            // if($requested_file->isDownloadExpired){

            //     if( $requested_file->download_count <= $requested_file->download_expired_at ){

            //         return response()->json(
            //             [
            //                 'status' => 'error',
            //                 'message' => 'Download limit reached'
            //             ] , 404
            //         );

            //     }

            // }

            $requested_file = UserFile::where('fileID' , $linkid)->first();

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'File found',
                    'file' => $requested_file
                ] , 200
            );

        }


    }


}
