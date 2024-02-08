<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateCourseController;
use App\Http\Controllers\CertificationCourseController;
use App\Http\Controllers\OffshoreCourseController;
use App\Http\Controllers\CourseController;






Route::get('/c', function () {
    echo 'hi';
});
 
Route::get('/c', function () {
    echo 'hi';
});
Route::middleware(['auth:sanctum'])->group(function () {
});

// routes for certificate courses
Route::post('/certificate-course/create', [CertificateCourseController::class, 'store']);
Route::get('/certificate-courses/{count}', [CertificateCourseController::class, 'get_list']);
Route::get('/certificate-course/{course_code}', [CertificateCourseController::class, 'get']);
Route::post('/certificate-course/{course_code}/edit', [CertificateCourseController::class, 'edit']);
Route::delete('/certificate-course/{course_code}/delete', [CertificateCourseController::class, 'delete']);

// routes for certification courses
Route::post('/certification-course/create', [CertificationCourseController::class, 'store']);
Route::get('/certification-courses/{count}', [CertificationCourseController::class, 'get_list']);
Route::get('/certification-course/{course_code}', [CertificationCourseController::class, 'get']);
Route::post('/certification-course/{course_code}/edit', [CertificationCourseController::class, 'edit']);
Route::delete('/certification-course/{course_code}/delete', [CertificationCourseController::class, 'delete']);

// routes for offshore courses
Route::post('/offshore-course/create', [OffshoreCourseController::class, 'store']);
Route::get('/offshore-courses/{count}', [OffshoreCourseController::class, 'get_list']);
Route::get('/offshore-course/{course_title}', [OffshoreCourseController::class, 'get']);
Route::post('/offshore-course/{course_title}/edit', [OffshoreCourseController::class, 'edit']);
Route::delete('/offshore-course/{course_title}/delete', [OffshoreCourseController::class, 'delete']);

// routes for courses

Route::get('/courses', [CourseController::class, 'get']);


