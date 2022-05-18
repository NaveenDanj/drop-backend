<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

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
                'download_expired_at' => 'required_if:isDownloadExpired,true'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // save the data to the database

            // split the string
            $file_name_to_array = explode('.', str_replace(".part" , "", $name) );
            $real_extension = $file_name_to_array[count($file_name_to_array) - 1];



            $uploadedFile = UserFile::create([
                'fileID' => uniqid(),
                'original_name' => str_replace(".part" , "" , $file->getClientOriginalName()),
                'name' => str_replace(".part" , "", $name),
                'extension' => $real_extension,
                'isPasswordProtected' => $request->boolean('isPasswordProtected'),
                'password' => $request->boolean('isPasswordProtected') == true ? Hash::make($request->string('password')) : null,
                'isDayExpired' => $request->boolean('isDayExpired'),
                'expired_at' => (int) $request->input('expired_at'),
                'isDownloadExpired' => $request->boolean('isDownloadExpired'),
                'download_expired_at' => (int) $request->input('download_expired_at'),
            ]);

            return response()->json([
                'success' => true,
                'uploadedFile' => $uploadedFile
            ], 200);
        }

        return response()->json(['uploaded' => true]);

    }

    // public function uploadFile(Request $request)
    // {
    //     $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

    //     if($receiver->isUploaded() === false){
    //         throw new UploadMissingFileException();
    //     }

    //     $fileReceived = $receiver->receive();

    //     if($fileReceived->isFinished()){

    //         $file = $fileReceived->getFile();

    //         $filename_with_ext = $file->getClientOriginalName();
    //         $filenameOnly = pathinfo($filename_with_ext , PATHINFO_FILENAME);
    //         $ext = $file->getClientOriginalExtension();

    //         $filename_to_store = $filenameOnly.'_'.time().'.'.$ext;

    //         // $path = $file->storeAs('public' , $filename_to_store);


    //         // $expire_time = null;
    //         // $expire_after = 10;

    //         // if($request->input('expire_at')){
    //         //     $expire_time = round(microtime(true) * 1000) + $request->input('expire_at') * 60 * 60 * 24 * 1000;
    //         //     $expire_after = (int)$request->input('expire_after');

    //         // }else{
    //         //     $expire_time = round(microtime(true) * 1000) + 1 * 60 * 60 * 24 * 1000;
    //         // }


    //         unlink($file->getPathname());

    //         return response()->json(["data" => 'Done'] ,  200);

    //     }

    //     $handler = $fileReceived->handler();

    //     return [

    //         'done' => $handler->getPercentageDone(),
    //         'status' => true

    //     ];

    // }



}
