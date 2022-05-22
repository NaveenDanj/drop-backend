<?php

namespace App\Http\Controllers;

use App\Models\ReleaseLog;
use Illuminate\Http\Request;

class ReleaseLogController extends Controller
{

    public function createRelease(Request $request){

        $request->validate([
            'release_date' => 'required|date',
            'version' => 'required|string|max:20',
            'release_log' => 'required|string|max:1000'
        ]);

        ReleaseLog::create([
            'release_date' => $request->release_date,
            'version' => $request->version,
            'release_log' => $request->release_log
        ]);

        return response()->json([
            'message' => 'Release Log Created Successfully'
        ]);

    }

    public function getReleaseLogs(){

        $releaseLogs = ReleaseLog::all();

        return response()->json([
            'releaseLogs' => $releaseLogs
        ]);

    }


}
