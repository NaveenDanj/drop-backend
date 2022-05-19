<?php

use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/upload-file' , [FileUploadController::class , 'store']);
Route::get('/getfile/{linkid}/{token}' , [FileDownloadController::class , 'downloadFile']);
Route::get('/checkfile/{linkid}' , [FileDownloadController::class , 'checkFileExists']);
Route::post('/check-password' , [FileDownloadController::class , 'checkPasswordCorrect']);
Route::get('/get-password-file/{linkid}/{token}/{password}' , [FileDownloadController::class , 'donwloadPasswordProtected']);
