<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\DropCleanupController;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ReleaseLogController;
use App\Http\Controllers\SendFileController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserResetPasswordController;
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


Route::prefix('auth')->group(function () {

    Route::post('login' , [UserAuthController::class , 'login'])->name('login');
    Route::post('register' , [UserAuthController::class , 'register'])->name('register');;
    Route::middleware('auth:sanctum')->get('logout' , [UserAuthController::class , 'logout'])->name('logout');

    Route::post('forgot-password' , [UserResetPasswordController::class , 'forgotPassword'])->name('forgotPassword');
    Route::post('reset-password' , [UserResetPasswordController::class , 'resetPassword'])->name('reset-password');
    Route::middleware('auth:sanctum')->post('update-account' , [UserAuthController::class , 'updateProfile'] );

});


Route::prefix('drop')->group(function () {
    Route::get('clean' , [DropCleanupController::class , 'DropCleanupClean']);
});

Route::prefix('release')->group(function () {
    Route::post('' , [ReleaseLogController::class , 'createRelease']);
    Route::get('' , [ReleaseLogController::class , 'getReleaseLogs']);
});

// protected dashboard route group
Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/userfiles/{userid}' , [DashboardController::class , 'userFiles']);
    Route::get('/userfilecount' , [DashboardController::class , 'getUserFileCount']);
});

Route::prefix('sendfile')->middleware('auth:sanctum')->group(function () {
    Route::post('/' , [SendFileController::class , 'sendFile']);
});
