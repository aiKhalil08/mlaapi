<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::post('/course/create', function (Request $request) {
    dd($request->all());
});

Route::get('/symlink', function (Request $request) {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
});

Route::get('/php_info', function (Request $request) {
    // \Illuminate\Support\Facades\Artisan::call('storage:link');
    phpinfo();
});