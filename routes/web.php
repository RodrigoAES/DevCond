<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\BilletController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('billet/download/{file}/{token}',[BilletController::class, 'downloadFile'])->name('file');

Route::get('local/temp/{file}', function (string $file, Request $request){
    if( ! $request->hasValidSignature()) {
        abort(401);
    }
    return Storage::disk('local')->get("warning-photos/$file");
})->name('local.temp');