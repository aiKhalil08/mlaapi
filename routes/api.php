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
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\FulfillmentController;
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
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/certificate-course/create', [CertificateCourseController::class, 'store']); 
    Route::post('/admin/certificate-course/{course_code}/edit', [CertificateCourseController::class, 'edit']);
    Route::delete('/admin/certificate-course/{course_code}/delete', [CertificateCourseController::class, 'delete']);
});
Route::get('/certificate-course/names', [CertificateCourseController::class, 'get_names']);
Route::get('/certificate-courses/{count}', [CertificateCourseController::class, 'get_list']);
Route::get('/certificate-course/{course_code}', [CertificateCourseController::class, 'get']);

// routes for certification courses
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/certification-course/create', [CertificationCourseController::class, 'store']);
    Route::post('/admin/certification-course/{course_code}/edit', [CertificationCourseController::class, 'edit']);
    Route::delete('/admin/certification-course/{course_code}/delete', [CertificationCourseController::class, 'delete']);
});
Route::get('/certification-course/names', [CertificationCourseController::class, 'get_names']);
Route::get('/certification-courses/{count}', [CertificationCourseController::class, 'get_list']);
Route::get('/certification-course/{course_code}', [CertificationCourseController::class, 'get']);

// routes for offshore courses
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/offshore-course/create', [OffshoreCourseController::class, 'store']);
    Route::post('/admin/offshore-course/{course_title}/edit', [OffshoreCourseController::class, 'edit']);
    Route::delete('/admin/offshore-course/{course_title}/delete', [OffshoreCourseController::class, 'delete']);
});
Route::get('/offshore-course/names', [OffshoreCourseController::class, 'get_names']);
Route::get('/offshore-courses/{count}', [OffshoreCourseController::class, 'get_list']);
Route::get('/offshore-course/{course_title}', [OffshoreCourseController::class, 'get']);

// routes for courses and resources

Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::get('/admin/courses', [CourseController::class, 'get']); 
    Route::get('/admin/resources', [ResourceController::class, 'get']);
});
Route::get('/{type}/enrolled-students/{course_identity}', [CourseController::class, 'get_enrolled_students']);




// routes for blogs
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/blog/create', [BlogController::class, 'store']);
    Route::post('/admin/blog/{course_title}/edit', [BlogController::class, 'edit']);
    Route::delete('/admin/blog/{heading}/delete', [BlogController::class, 'delete']);
});
Route::get('/blog/{heading}', [BlogController::class, 'get']);
Route::get('/blog/{heading}/post', [BlogController::class, 'get_post']);
Route::get('/blogs/{count}', [BlogController::class, 'get_list']);




// routes for testimonials
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/testimonial/create', [TestimonialController::class, 'store']);
    Route::post('/admin/testimonial/{name}/edit', [TestimonialController::class, 'edit']);
    Route::delete('/admin/testimonial/{name}/delete', [TestimonialController::class, 'delete']);
});
Route::get('/testimonial/{name}', [TestimonialController::class, 'get']);
// Route::get('/testimonial/{heading}/post', [TestimonialController::class, 'get_post']);
Route::get('/testimonials/{count}', [TestimonialController::class, 'get_list']);



// routes for events
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/event/create', [EventController::class, 'store']);
    Route::post('/admin/event/{course_title}/edit', [EventController::class, 'edit']);
    Route::delete('/admin/event/{course_title}/delete', [EventController::class, 'delete']);
});
Route::get('/event/{name}', [EventController::class, 'get']);
Route::get('/events/{count}', [EventController::class, 'get_list']);



// routes for sales
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::get('/admin/sale/{id}', [SaleController::class, 'get']);
    Route::post('/admin/sale', [SaleController::class, 'store']);
    Route::get('/admin/sales', [SaleController::class, 'get_all']);
});


// routes for cohorts
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/cohort', [CohortController::class, 'store']);
    Route::get('/admin/cohort/{name}/start', [CohortController::class, 'start']);
    Route::get('/admin/cohort/{name}/conclude', [CohortController::class, 'conclude']);
    Route::get('/admin/cohort/{name}/abort', [CohortController::class, 'abort']);
    Route::get('/admin/cohort/{name}/delete', [CohortController::class, 'delete']);
    Route::get('/admin/cohorts', [CohortController::class, 'get_all']);
    Route::get('/admin/cohort/names', [CohortController::class, 'get_names']);
    Route::get('/admin/cohort/{name}', [CohortController::class, 'get']);
    Route::post('/admin/cohort/{name}/notify-students', [CohortController::class, 'notify_students']);
    Route::get('/admin/cohort/{name}/students', [CohortController::class, 'all_students_showing_those_in_cohort']);
    Route::post('/admin/cohort/{name}/students', [CohortController::class, 'add_students']);
    Route::get('/admin/cohort/{name}/certificates', [CohortController::class, 'get_students_certificates']);
    Route::get('/admin/cohort/{name}/edit', [CohortController::class, 'get_cohort_for_edit']);
    Route::post('/admin/cohort/{name}/edit', [CohortController::class, 'edit']);
});

// routes for certificates
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::post('/admin/certificates', [CertificateController::class, 'upload']);
});
Route::get('/certificate/{type}', [CertificateController::class, 'get']);

// routes for fulfillments
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::get('/admin/fulfillment/{id}', [FulfillmentController::class, 'get']);
    Route::get('/admin/fulfillments', [FulfillmentController::class, 'get_all']);
    Route::post('/admin/fulfillment', [FulfillmentController::class, 'fulfill']);
});



// routes for requests

Route::post('/request/create', [ContactRequestController::class, 'store']);
Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::get('/admin/request/{last_name}/{created_at}', [ContactRequestController::class, 'get']);
    Route::get('/admin/requests/{count}', [ContactRequestController::class, 'get_list']);
});

// routes for users and affiliates

Route::middleware(['should_use:admin-jwt', 'jwt_auth'])->group(function () {
    Route::get('/admin/users', [StudentController::class, 'get_all']);
    Route::get('/admin/affiliates', [AffiliateController::class, 'get_all']);
    Route::get('/admin/user/{gmail}', [StudentController::class, 'get']);
    Route::get('/admin/affiliate/{gmail}', [AffiliateController::class, 'get']);
});



// routes for students

Route::post('/student', [StudentController::class, 'store']);
Route::post('/student/confirm-email', [StudentController::class, 'confirm_email']); //confirms email during registraion
Route::post('/student/send-otp', [StudentController::class, 'send_otp']); //sends otp
Route::middleware(['should_use:student-jwt', 'jwt_auth'])->group(function () {
    Route::post('/student/cart', [CartController::class, 'add']);
    Route::get('/student/cart', [StudentController::class, 'get_cart']);
    Route::post('/student/remove-from-cart', [CartController::class, 'remove']);
    Route::get('/student/courses', [StudentController::class, 'get_enrolled_courses']);
    Route::post('/student/get_carted_course', [StudentController::class, 'get_course']);
    Route::post('/student/get_enrolled_course', [StudentController::class, 'get_course']);
    Route::post('/student/watchlist', [StudentController::class, 'add_event_to_watchlist']);
    Route::get('/student/watchlist', [StudentController::class, 'get_event_watchlist']);
    Route::get('/student/watchlist/{event}', [StudentController::class, 'get_event_watchlist_event']);
    Route::get('/student/profile', [StudentController::class, 'get_profile']);
    Route::post('/student/profile', [StudentController::class, 'update_profile']);
    Route::get('/student/become-affiliate', [AffiliateController::class, 'create_referral_code']);
    Route::get('/student/renew-referral-code', [AffiliateController::class, 'renew_referral_code']);
    Route::get('/student/load-affiliate-portal', [AffiliateController::class, 'load_affiliate_portal']);
    Route::post('/student/payout', [FulfillmentController::class, 'add']);
    Route::get('/student/trending-courses', [CourseController::class, 'trending_courses']);
    Route::get('/student/certificates', [CertificateController::class, 'get_my_certificates']);
    Route::get('/student/download-certificate', [CertificateController::class, 'download_certificate']);
});
Route::get('/student/{phone_number}', [StudentController::class, 'get']);
Route::get('/students/{count}', [StudentController::class, 'get_list']);







// routes for authentication
Route::post('/login/admin', [AuthController::class, 'admin_login']); //authenticates admin
Route::post('/login1/{type}', [AuthController::class, 'login_one']); //validates credentials and sends otp
Route::post('/login2/{type}', [AuthController::class, 'login_two']); //validates otp and logs in user
Route::post('/login/resend-otp/{type}/{email}', [AuthController::class, 'resend_otp']); //resends otp 
Route::get('/test-auth', function() {
    return response()->json(['user'=> auth()->guard('student-jwt')->user(), 'message'=>bcrypt('mlaadmin12345')], 200);
})->middleware(['should_use:student-jwt', 'jwt_auth']);



// routes for password confirmation
Route::post('/admin/confirm-password', [AuthController::class, 'confirm_admin_password'])
->middleware(['should_use:admin-jwt', 'jwt_auth']);; //confirms authenticated admin's password

Route::post('/student/confirm-password', [AuthController::class, 'confirm_student_password'])
->middleware(['should_use:student-jwt', 'jwt_auth']);; //confirms authenticated student's password



//routes for password reset
Route::post('/send_password_reset_link/{type}', [PasswordController::class, 'send_reset_link']);
Route::post('/validate_link', [PasswordController::class, 'validate_link']);
Route::post('/reset_password', [PasswordController::class, 'reset_password']);




//routes for cart



// routes for event watchlist





// routes for admin-dashboard
Route::get('/admin-dashboard', function (Request $request) {
    $dashboard = [];
    $dashboard['certificate-courses_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.certificate_courses')[0]->count;
    $dashboard['certification-courses_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.certification_courses')[0]->count;
    $dashboard['offshore-courses_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.offshore_courses')[0]->count;
    $dashboard['events_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.events')[0]->count;
    $dashboard['blogs_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.blogs')[0]->count;
    $dashboard['testimonials_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.testimonials')[0]->count;
    $dashboard['requests_count']['all'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.contact_requests')[0]->count;
    $dashboard['requests_count']['unread'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.contact_requests where viewed = 0')[0]->count;
    // $dashboard['students_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.students')[0]->count;
    // $dashboard['affiliates_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.affiliates')[0]->count;
    // $dashboard['tutors_count'] = DB::select('select count(id) as count from '.env('DB_DATABASE').'.tutors')[0]->count;
    return response()->json($dashboard, 200);
})->middleware(['should_use:admin-jwt', 'jwt_auth']);



// fetches student's name based on provided email
Route::get('/admin/fetch_student_name/{email}', [StudentController::class, 'fetch_name'])->middleware(['should_use:admin-jwt', 'jwt_auth']);

// routes for affiliate
Route::get('/fetch-affiliate/{code}', [AffiliateController::class, 'fetch_affiliate']);#->middleware(['should_use:admin-jwt', 'jwt_auth']);

