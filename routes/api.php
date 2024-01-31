<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateCourseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/c', function () {
    echo 'hi';
});
Route::middleware(['auth:sanctum'])->group(function () {
});

Route::post('/course/create', [CertificateCourseController::class, 'store']);
Route::get('/courses/{count}', [CertificateCourseController::class, 'get_list']);
Route::get('/course/{course_code}', [CertificateCourseController::class, 'get']);
// Route::get('/course/{course_code}', function () {
//     var_dump(request()->schemeAndHttpHost());
// });