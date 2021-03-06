<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;


class FileUploadController extends Controller
{

    public function store(Request $request)
    {

        $file = $request->file('file');

        if($file == null){
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $path = Storage::disk('local')->path("chunks/{$file->getClientOriginalName()}");

        File::append($path, $file->get());

        if ($request->has('is_last') && $request->boolean('is_last')) {

            // check user exists for given user id and email
            $user = null;

            if($request->user_id && $request->email){
                $user = User::where('id' , $request->user_id)->where('email' , $request->email)->first();
            }

            // check if user file count is exceeded
            if($user != null){
                $userFileCount = UserFile::where('user_id' , $request->user()->id)->count();
                $userFileLimit = $user->file_limit;

                if($userFileCount >= $userFileLimit){
                    return response()->json(['error' => 'User file count exceeded'], 400);
                }

            }

            $name = basename($path, '.part');
            $name = uniqid() . '_' . $name;
            $dest_path = Storage::disk('local')->path("files/{$name}");
            File::move($path, $dest_path);

            // validate request
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
                'isPasswordProtected' => 'required',
                'password' => 'required_if:isPasswordProtected,true',
                'isDayExpired' => 'required',
                'expired_at' => 'required_if:isDayExpired,true',
                'isDownloadExpired' => 'required',
                'download_expired_at' => 'required_if:isDownloadExpired,true',
                'user_id' =>  'integer',
                'email' => 'string|email'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }


            // split the string
            $file_name_to_array = explode('.', str_replace(".part" , "", $name) );
            $real_extension = $file_name_to_array[count($file_name_to_array) - 1];

            // save the data to the database
            $uploadedFile = UserFile::create([
                'fileID' => uniqid(),
                'original_name' => str_replace(".part" , "" , $file->getClientOriginalName()),
                'name' => str_replace(".part" , "", $name),
                'extension' => $real_extension,
                'isPasswordProtected' => $request->boolean('isPasswordProtected'),
                'password' => $request->boolean('isPasswordProtected') == true ? Hash::make($request->input('password')) : null,
                'isDayExpired' => $request->boolean('isDayExpired'),
                'expired_at' => (int) $request->input('expired_at'),
                'isDownloadExpired' => $request->boolean('isDownloadExpired'),
                'download_expired_at' => (int) $request->input('download_expired_at'),
                'user_id' => $user == null ? null : $user->id
            ]);

            return response()->json([
                'success' => true,
                'uploadedFile' => $uploadedFile
            ], 200);
        }

        return response()->json(['uploaded' => true]);

    }


}
