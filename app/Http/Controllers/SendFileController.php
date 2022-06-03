<?php

namespace App\Http\Controllers;

use App\Models\SendFile;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\Request;

class SendFileController extends Controller
{
    public function sendFile(Request $request){

        $request->validate([
            'to_email' => 'required',
            'file_id' => 'required|integer',
            'subject' => 'string|max:50',
            'message' => 'string:max:512',
        ]);

        // get the to user
        $to_user = User::where('email' , $request->to_email)->first();

        // check if the file exists
        $file_check = UserFile::where('id' , $request->file_id)->first();
        if($file_check == null){
            return response()->json([
                'message' => 'File not found'
            ] , 404);
        }



        if($to_user){

            // prevent sending to yourself
            if($to_user->id == $request->user()->id){
                return response()->json([
                    'message' => 'You cannot send a file to yourself'
                ] , 400);
            }

            // we are sending the file to his account
            $sendFile = new SendFile();
            $sendFile->from_user = $request->user()->id;
            $sendFile->to_user = $to_user->id;
            $sendFile->to_email = $request->to_email;
            $sendFile->file_id = $request->file_id;
            $sendFile->subject = $request->subject;
            $sendFile->message = $request->message;
            $sendFile->save();
        }else{

            // prevent sending to yourself
            if($request->user()->email == $request->to_email){
                return response()->json([
                    'message' => 'You cannot send a file to yourself'
                ] , 400);
            }

            // we are sending the file to the email
            $sendFile = new SendFile();
            $sendFile->from_user = $request->user()->id;
            $sendFile->to_email = $request->to_email;
            $sendFile->file_id = $request->file_id;
            $sendFile->subject = $request->subject;
            $sendFile->message = $request->message;
            $sendFile->save();
            $request->user()->sendFileNotification($request->fileID);
        }



        return response()->json([
            'message' => 'File sent successfully'
        ] , 200);


    }
}
