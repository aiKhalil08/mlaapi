<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateCourseController;
use App\Http\Controllers\CertificationCourseController;
use App\Http\Controllers\OffshoreCourseController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\DB;





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

// routes for blogs
Route::post('/blog/create', [BlogController::class, 'store']);
Route::get('/blog/{heading}', [BlogController::class, 'get']);
Route::get('/blog/{heading}/post', [BlogController::class, 'get_post']);
Route::get('/blogs/{count}', [BlogController::class, 'get_list']);
Route::post('/blog/{course_title}/edit', [BlogController::class, 'edit']);
Route::delete('/blog/{heading}/delete', [BlogController::class, 'delete']);

// routes for testimonials
Route::post('/testimonial/create', [TestimonialController::class, 'store']);
Route::get('/testimonial/{name}', [TestimonialController::class, 'get']);
// Route::get('/testimonial/{heading}/post', [TestimonialController::class, 'get_post']);
Route::get('/testimonials/{count}', [TestimonialController::class, 'get_list']);
Route::post('/testimonial/{name}/edit', [TestimonialController::class, 'edit']);
Route::delete('/testimonial/{name}/delete', [TestimonialController::class, 'delete']);

// routes for events
Route::post('/event/create', [EventController::class, 'store']);
Route::get('/event/{name}', [EventController::class, 'get']);
// Route::get('/event/{na}/post', [BlogController::class, 'get_post']);
Route::get('/events/{count}', [EventController::class, 'get_list']);
Route::post('/event/{course_title}/edit', [EventController::class, 'edit']);
Route::delete('/event/{course_title}/delete', [EventController::class, 'delete']);


// routes for resources

Route::get('/resources', [ResourceController::class, 'get']);

// routes for admin-dashboard

Route::get('/admin-dashboard', function (Request $request) {
    $dashboard = [];
    $dashboard['certificate-courses_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.certificate_courses')[0]->count;
    $dashboard['certification-courses_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.certification_courses')[0]->count;
    $dashboard['offshore-courses_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.offshore_courses')[0]->count;
    $dashboard['events_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.events')[0]->count;
    $dashboard['blogs_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.blogs')[0]->count;
    $dashboard['testimonials_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.testimonials')[0]->count;
    // $dashboard['students_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.students')[0]->count;
    // $dashboard['affiliates_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.affiliates')[0]->count;
    // $dashboard['tutors_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.tutors')[0]->count;
    return response()->json($dashboard, 200);
});

