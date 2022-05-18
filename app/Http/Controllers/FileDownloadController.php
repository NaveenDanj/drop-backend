<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class FileDownloadController extends Controller
{

    public function downloadFile(Request $request , $linkid , $token){
    	// $myFile = storage_path("app/public/remember the name_1632552046.mp4");

        $requested_file = UserFile::where('fileID' , $linkid)->first();

        if($requested_file == null){
            return response()->json(['error' => 'File not found'], 400);
        }



        // check if file is expired by days
        // get the created at timestamp
        $created_at_timestamp = strtotime($requested_file->created_at) * 1000;
        $expire_timestamp =$created_at_timestamp + $requested_file->expired_at;

        // return response()->json([
        //     'created_at_timestamp' => $created_at_timestamp,
        //     'expire_timestamp' => $expire_timestamp,
        //     'current_timestamp' => time()
        // ]);

        if( $expire_timestamp < time() * 1000){

            // check if the file exists
            if(Storage::disk('local')->exists("files/{$requested_file->name}")){
                // if exists delete it
                Storage::disk('local')->delete("files/{$requested_file->name}");
            }

            return response()->json(['error' => 'File expired'], 400);
        }


        // check if file is expired by download
        if($requested_file->isDownloadExpired == 1){

            if( $requested_file->download_count >= $requested_file->download_expired_at){

                // check if the file exists
                if(Storage::disk('local')->exists("files/{$requested_file->name}")){
                    // if exists delete it
                    Storage::disk('local')->delete("files/{$requested_file->name}");
                }

                return response()->json(['error' => 'File expired'], 400);
            }
        }


        $myFile =  storage_path('app/files/'.$requested_file->name);

        $decrypted_token = Crypt::decrypt($token);
        $got_file_id = explode( "-" , $decrypted_token)[0];
        $got_expire_timestamp = explode( "-" , $decrypted_token)[1];

        // check if file id is correct
        if( $got_file_id != $requested_file->fileID){
            return response()->json(['error' => 'Invalid token' ], 400);
        }

        // if timestamp is not older than 5 minutes
        if( (int)$got_expire_timestamp + 300000  < time() * 1000 ){
            return response()->json(['error' => 'Token expired'], 400);
        }

        // check if file exists
        if(!Storage::disk('local')->exists("files/{$requested_file->name}")){
            return response()->json(['error' => 'File not found'], 400);
        }

        // increment the download count
        $requested_file->download_count = $requested_file->download_count + 1;
        $requested_file->save();

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

            // check if file is expired by download
            if($requested_file->isDownloadExpired == 1){

                if( $requested_file->download_count >= $requested_file->download_expired_at){

                    // check if the file exists
                    if(Storage::disk('local')->exists("files/{$requested_file->name}")){
                        // if exists delete it
                        Storage::disk('local')->delete("files/{$requested_file->name}");
                    }

                    return response()->json(['error' => 'Download limit reached' ], 400);
                }
            }


            // check if file is expired by days
            // get the created at timestamp
            $created_at_timestamp = strtotime($requested_file->created_at) * 1000;
            $expire_timestamp =$created_at_timestamp + $requested_file->expired_at;

            // return response()->json([
            //     'created_at_timestamp' => $created_at_timestamp,
            //     'expire_timestamp' => $expire_timestamp,
            //     'current_timestamp' => time()
            // ]);

            if( $expire_timestamp < time() * 1000){

                // check if the file exists
                if(Storage::disk('local')->exists("files/{$requested_file->name}")){
                    // if exists delete it
                    Storage::disk('local')->delete("files/{$requested_file->name}");
                }

                return response()->json(['error' => 'File expired'], 400);
            }



            $requested_file = UserFile::where('fileID' , $linkid)->first();
            // get the current time in miliseconds
            $current_time = round(microtime(true) * 1000);


            $token = Crypt::encrypt( "$requested_file->fileID" . "-" . $current_time);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'File found',
                    'file' => $requested_file,
                    'token' => $token
                ] , 200
            );

        }


    }


}
