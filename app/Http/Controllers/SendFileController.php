<?php

namespace App\Http\Controllers;

use App\Models\SendFile;
use App\Models\User;
use Illuminate\Http\Request;

class SendFileController extends Controller
{
    public function sendFile(Request $request){

        $request->validate([
            'to_email' => 'required',
            'file_id' => 'required',
            'subject' => 'string|max:50',
            'message' => 'string:max:512',
        ]);

        // get the to user
        $to_user = User::where('email' , $request->to_email)->first();

        if($to_user){
            // we are sending the file to his account
            $sendFile = new SendFile();
            $sendFile->from_user = $request->user()->id;
            $sendFile->to_user = $to_user;
            $sendFile->to_email = $request->to_email;
            $sendFile->file_id = $request->file_id;
            $sendFile->subject = $request->subject;
            $sendFile->message = $request->message;
            $sendFile->save();
        }else{
            // we are sending the file to the email
            $sendFile = new SendFile();
            $sendFile->from_user = $request->user()->id;
            $sendFile->to_email = $request->to_email;
            $sendFile->file_id = $request->file_id;
            $sendFile->subject = $request->subject;
            $sendFile->message = $request->message;
            $sendFile->save();




        }



        return response()->json([
            'message' => 'File sent successfully'
        ] , 200);


    }
}
