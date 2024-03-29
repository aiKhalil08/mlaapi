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


// Route::post('/course/create', function (Request $request) {
//     dd($request->all());
// });

Route::get('/symlink', function (Request $request) {
    // echo 'hi how are you';
    \Illuminate\Support\Facades\Artisan::call('storage:link');
});

Route::get('some', function () {
    return ['Hi, you are here'];
})->middleware(\App\Http\Middleware\Some::class);

Route::post('somepost', function(Request $request) {
    var_dump($request->cookies());
})->middleware(\App\Http\Middleware\Some::class);

// Route::get('/hi', function (Request $req) {
//     echo 'hi how are you';
// });

Route::get('/php_info', function (Request $request) {
    // \Illuminate\Support\Facades\Artisan::call('storage:link');
    phpinfo();
});