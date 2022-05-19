<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
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

        // check if file is password protected
        if($requested_file->isPasswordProtected == 1){

            // check if the token is correct
            if($requested_file->password != $token){
                return response()->json(['error' => 'this file is password protected!'], 400);
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


    public function checkPasswordCorrect(Request $request){

        // validate inputs
        $this->validate($request, [
            'linkid' => 'required',
            'password' => 'required'
        ]);

        $requested_file = UserFile::where('fileID' , $request->linkid)->first();

        if ($requested_file === null) {

            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'File not found'
                ] , 404
            );

        }

        // check if password protected
        if($requested_file->isPasswordProtected == 0){
            return response()->json(['error' => 'File is not password protected'], 400);
        }

        // check if password is correct
        if(Hash::check($request->password, $requested_file->password)){
            $requested_file = UserFile::where('fileID' , $request->linkid)->first();

            $token = Crypt::encrypt( "$requested_file->fileID" . "-" . round(microtime(true) * 1000));

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Password correct',
                    'file' => $requested_file,
                    'token' => $token
                ] , 200
            );

        }else{
            return response()->json(['error' => 'Password incorrect'], 400);
        }




    }


    public function donwloadPasswordProtected(Request $request , $linkid , $token , $password){

        // validate inputs
        if($linkid == null || $token == null){
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $requested_file = UserFile::where('fileID' , $linkid)->first();

        if ($requested_file === null) {

            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'File not found'
                ] , 404
            );

        }

        // check if password protected
        if($requested_file->isPasswordProtected == 0){
            return response()->json(['error' => 'File is not password protected'], 400);
        }

        // check if token is correct
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


        // check if password is correct hashed
        if(Hash::check($password, $requested_file->password)){

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

            // increment the download count
            $requested_file->download_count = $requested_file->download_count + 1;
            $requested_file->save();

            $myFile =  storage_path('app/files/'.$requested_file->name);

            return response()->download($myFile);


        }else{

            return response()->json(['error' => 'Wrong password'], 400);


        }



    }

}
