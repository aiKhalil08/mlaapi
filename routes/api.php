<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ExternalUserController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\AssignmentController;



// routes generic to unauthenticated users
Route::post('/auth/user', [UserController::class, 'store']);
Route::post('/auth/user/verify-email', [UserController::class, 'verifyEmail']);


// routes generic to all authenticated users
Route::middleware(['jwt_auth'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'get_profile']);
    Route::post('/user/profile', [UserController::class, 'update_profile']);

    // routes regarding affiliate
    Route::get('/user/become-affiliate', [AffiliateController::class, 'create_referral_code']);
    Route::get('/user/renew-referral-code', [AffiliateController::class, 'renew_referral_code']);
});
Route::get('/user/load-affiliate-portal', [AffiliateController::class, 'load_affiliate_portal']);


// routes for deleting or updating user roles
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/user/make-or-revoke/{role_name}', [UserController::class, 'makeOrRevoke']);
    Route::delete('/user/{email}', [UserController::class, 'delete']);
});


// routes for external users management
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/external-user', [ExternalUserController::class, 'store']);
    Route::get('/admin/external-user/{email}', [ExternalUserController::class, 'get']);
    Route::delete('/admin/external-user/{email}', [ExternalUserController::class, 'delete']);
    Route::post('/admin/external-user/{email}/edit', [ExternalUserController::class, 'update']);
    Route::get('/admin/external-users', [ExternalUserController::class, 'getAll']);
});



// routes for certificate courses
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/certificate-course/create', [CertificateCourseController::class, 'store']); 
    Route::post('/admin/certificate-course/{course_code}/edit', [CertificateCourseController::class, 'edit']);
    Route::delete('/admin/certificate-course/{course_code}/delete', [CertificateCourseController::class, 'delete']);
});
Route::get('/certificate-courses/{count}', [CertificateCourseController::class, 'get_list']);
Route::get('/certificate-course/names', [CertificateCourseController::class, 'get_names']);
Route::get('/certificate-course/{course_code}', [CertificateCourseController::class, 'get']);

// routes for certification courses
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/certification-course/create', [CertificationCourseController::class, 'store']);
    Route::post('/admin/certification-course/{course_code}/edit', [CertificationCourseController::class, 'edit']);
    Route::delete('/admin/certification-course/{course_code}/delete', [CertificationCourseController::class, 'delete']);
});
Route::get('/certification-course/names', [CertificationCourseController::class, 'get_names']);
Route::get('/certification-courses/{count}', [CertificationCourseController::class, 'get_list']);
Route::get('/certification-course/{course_code}', [CertificationCourseController::class, 'get']);

// routes for offshore courses
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/offshore-course/create', [OffshoreCourseController::class, 'store']);
    Route::post('/admin/offshore-course/{course_title}/edit', [OffshoreCourseController::class, 'edit']);
    Route::delete('/admin/offshore-course/{course_title}/delete', [OffshoreCourseController::class, 'delete']);
});
Route::get('/offshore-course/names', [OffshoreCourseController::class, 'get_names']);
Route::get('/offshore-courses/{count}', [OffshoreCourseController::class, 'get_list']);
Route::get('/offshore-course/{course_title}', [OffshoreCourseController::class, 'get']);


// routes for courses and resources
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::get('/admin/courses', [CourseController::class, 'get']); 
    Route::get('/admin/resources', [ResourceController::class, 'get']);
});
Route::get('/{type}/enrolled-students/{course_identity}', [CourseController::class, 'get_enrolled_students']);




// routes for blogs
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/blog/create', [BlogController::class, 'store']);
    Route::post('/admin/blog/{course_title}/edit', [BlogController::class, 'edit']);
    Route::delete('/admin/blog/{heading}/delete', [BlogController::class, 'delete']);
});
Route::get('/blog/{heading}', [BlogController::class, 'get']);
Route::get('/blog/{heading}/post', [BlogController::class, 'get_post']);
Route::get('/blogs/{count}', [BlogController::class, 'get_list']);




// routes for testimonials
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/testimonial/create', [TestimonialController::class, 'store']);
    Route::post('/admin/testimonial/{name}/edit', [TestimonialController::class, 'edit']);
    Route::delete('/admin/testimonial/{name}/delete', [TestimonialController::class, 'delete']);
});
Route::get('/testimonial/{name}', [TestimonialController::class, 'get']);
// Route::get('/testimonial/{heading}/post', [TestimonialController::class, 'get_post']);
Route::get('/testimonials/{count}', [TestimonialController::class, 'get_list']);



// routes for events
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/event/create', [EventController::class, 'store']);
    Route::post('/admin/event/{name}/edit', [EventController::class, 'edit']);
    Route::delete('/admin/event/{name}/delete', [EventController::class, 'delete']);
});
Route::get('/event/{name}', [EventController::class, 'get']);
Route::get('/events/{count}', [EventController::class, 'get_list']);
Route::post('/event/{name}/register', [EventController::class, 'register']);
Route::get('/event/registration/{registration_id}', [EventController::class, 'getRegistration']);
Route::get('/event/{name}/registrations', [EventController::class, 'getRegistrations']);



// routes for sales
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::get('/admin/sale/{id}', [SaleController::class, 'get']);
    Route::post('/admin/sale', [SaleController::class, 'store']);
    Route::get('/admin/sales', [SaleController::class, 'get_all']);
});


// routes for cohorts
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/cohort', [CohortController::class, 'store']);
    Route::get('/admin/cohort/{name}/start', [CohortController::class, 'start']);
    Route::get('/admin/cohort/{name}/conclude', [CohortController::class, 'conclude']);
    Route::get('/admin/cohort/{name}/abort', [CohortController::class, 'abort']);
    Route::get('/admin/cohort/{name}/delete', [CohortController::class, 'delete']);
    Route::get('/admin/cohorts/{type?}', [CohortController::class, 'get_all']);
    Route::get('/admin/cohort/{name}', [CohortController::class, 'get']);
    Route::post('/admin/cohort/{name}/notify-students', [CohortController::class, 'notify_students']);
    Route::get('/admin/cohort/{name}/students', [CohortController::class, 'all_students_showing_those_in_cohort']);
    Route::post('/admin/cohort/{name}/students', [CohortController::class, 'updateStudents']);
    Route::get('/admin/cohort/{name}/certificates', [CohortController::class, 'get_students_certificates']);
    Route::get('/admin/cohort/{name}/edit', [CohortController::class, 'get_cohort_for_edit']);
    Route::post('/admin/cohort/{name}/edit', [CohortController::class, 'edit']);
});

Route::get('/ref', function() {
    var_dump(\App\Models\Referral::where('id', 24)->with(['code', 'referrer'])->first()); return null;
});

// routes for certificates
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/certificates', [CertificateController::class, 'upload']);
});
Route::get('/certificate/{type}', [CertificateController::class, 'get']);

// routes for fulfillments
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::get('/admin/fulfillment/{id}', [FulfillmentController::class, 'get']);
    Route::post('/admin/fulfillment', [FulfillmentController::class, 'fulfill']);
});
Route::get('/admin/fulfillments', [FulfillmentController::class, 'get_all']);



// routes for quiz

//routes for quiz admin
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/quiz', [QuizController::class, 'store']);
    Route::get('/admin/quizzes', [QuizController::class, 'getAll']);
    Route::get('/admin/quiz/{title}', [QuizController::class, 'get']);
    Route::delete('/admin/quiz/{title}', [QuizController::class, 'delete']);
    Route::post('/admin/quiz/{title}/edit', [QuizController::class, 'update']);
    Route::get('/admin/quiz/{title}/all-students', [QuizController::class, 'getAllStudents']);
    Route::post('/admin/quiz/{title}/add-question', [QuizController::class, 'addQuestion']);
    Route::post('/admin/quiz/{title}/edit-question/{question_id}', [QuizController::class, 'editQuestion']);
    Route::delete('/admin/quiz/{title}/delete-question/{question_id}', [QuizController::class, 'deleteQuestion']);
    Route::get('/admin/quiz/{title}/get-question/{question_id}', [QuizController::class, 'getQuestion']);
    Route::get('/admin/quiz/{title}/questions', [QuizController::class, 'getQuestions']);
    Route::get('/admin/quiz/{title}/assignments', [QuizController::class, 'getAssignments']);
    Route::post('/admin/quiz/{title}/assignments', [QuizController::class, 'updateAssignments']);
    // routes for assignments
    Route::post('/admin/quiz/add-assignment', [QuizController::class, 'addAssignment']);
});

//routes for assignments
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin/assignment', [AssignmentController::class, 'store']);
    Route::get('/admin/assignment/{name}', [AssignmentController::class, 'get']);
    Route::delete('/admin/assignment/{name}', [AssignmentController::class, 'delete']);
    Route::post('/admin/assignment/{name}/edit', [AssignmentController::class, 'edit']);
    Route::get('/admin/assignments', [AssignmentController::class, 'getAll']);
    Route::get('/admin/assignment/{name}/all-students', [AssignmentController::class, 'getAllStudents']);
    Route::get('/admin/assignment/{name}/students', [AssignmentController::class, 'getStudents']);
    Route::post('/admin/assignment/{name}/students', [AssignmentController::class, 'updateStudents']);
    Route::post('/admin/assignment/{name}/notify-students', [AssignmentController::class, 'notifyStudents']);
    Route::get('/admin/assignment/{name}/{new_status}', [AssignmentController::class, 'changeStatus']);
});

//routes for assignment takers (students and external users)
Route::middleware(['jwt_auth','must_be:student,external_user'])->group(function () {
    Route::get('/assignments/pending', [AssignmentController::class, 'getPendingAssignments']); // gets all current assignments for user
    Route::get('/assignments/completed', [AssignmentController::class, 'getCompletedAssignments']); // gets all done assignments for user
    Route::get('/assignment/{name}', [AssignmentController::class, 'getAssignment']);
    Route::get('/assignment/{name}/questions', [AssignmentController::class, 'getAssignmentQuestions']);
    Route::post('/assignment/{name}/submit', [AssignmentController::class, 'submitAssignment']);
    Route::get('/assignment/{name}/review', [AssignmentController::class, 'getAssignmentReview']);
    // Route::post('/admin/quiz/{title}/notify', [QuizController::class, 'notify']);
});



// routes for requests
Route::post('/request/create', [ContactRequestController::class, 'store']);
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::get('/admin/request/{last_name}/{created_at}', [ContactRequestController::class, 'get']);
    Route::get('/admin/requests/{count}', [ContactRequestController::class, 'get_list']);
});

// routes for students and affiliates
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::get('/admin/students', [StudentController::class, 'get_all']);
    Route::get('/admin/student/{email}', [StudentController::class, 'get']);
    Route::get('/admin/users', [UserController::class, 'getAll']);
    Route::get('/admin/user/{email}', [UserController::class, 'get']);
    Route::delete('/admin/student/{email}/delete', [StudentController::class, 'delete']);
    Route::get('/admin/affiliates', [AffiliateController::class, 'get_all']);
    Route::get('/admin/affiliate/{email}', [AffiliateController::class, 'get']);
});


// routes for audit trails
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::get('/admin/audit-trails', [AuditController::class, 'get_trails']);
    Route::get('/admin/audit-trail/{id}', [AuditController::class, 'get']);
});


// routes for admins
Route::middleware(['jwt_auth','must_be:admin'])->group(function () {
    Route::post('/admin', [AdminController::class, 'store']);
    Route::get('/admin/{email}', [AdminController::class, 'get']);
    Route::get('/admin/{email}/permissions', [AdminController::class, 'getPermissions']);
    Route::post('/admin/{email}/permissions', [AdminController::class, 'updatePermissions']);
    Route::get('/admins', [AdminController::class, 'get_all']);
    Route::post('/admin/{email}/update', [AdminController::class, 'update']);
    Route::delete('/admin/{email}', [AdminController::class, 'delete']);
});


// routes specific to students
Route::post('/student', [StudentController::class, 'store']);
Route::post('/student/confirm-email', [StudentController::class, 'confirm_email']); //confirms email during registraion
Route::post('/student/send-otp', [StudentController::class, 'send_otp']); //sends otp
Route::middleware(['jwt_auth','must_be:student'])->group(function () {
    Route::post('/student/cart', [StudentController::class, 'addCourseToCart']);
    Route::get('/student/cart', [StudentController::class, 'getCart']);
    Route::post('/student/remove-from-cart', [StudentController::class, 'removeFromCart']);
    Route::post('/student/watchlist', [StudentController::class, 'addEventToWatchlist']);
    Route::get('/student/watchlist', [StudentController::class, 'getEventWatchlist']);
    Route::get('/student/watchlist/{event}', [StudentController::class, 'getEventFromWatchlist']);
    Route::get('/student/courses', [StudentController::class, 'getEnrolledCourses']);
    Route::post('/student/get_carted_course', [StudentController::class, 'getCourse']);
    Route::post('/student/get_enrolled_course', [StudentController::class, 'getCourse']);
    Route::post('/student/payout', [FulfillmentController::class, 'add']);
    Route::get('/student/trending-courses', [CourseController::class, 'trendingCourses']);
    Route::get('/student/certificates', [StudentController::class, 'getMyCertificates']);
    Route::get('/student/download-certificate', [CertificateController::class, 'downloadCertificate']);
});
Route::get('/student/{phone_number}', [StudentController::class, 'get']);
Route::get('/students/{count}', [StudentController::class, 'get_list']);





// routes for authentication
Route::post('/login/admin', [AuthController::class, 'admin_login']); //authenticates admin
Route::post('/login1', [AuthController::class, 'login_one']); //validates credentials and sends otp
Route::post('/login2', [AuthController::class, 'login_two']); //validates otp and logs in user
Route::post('/login/resend-otp/{email}', [AuthController::class, 'resend_otp']); //resends otp 
// Route::get('/test-auth', function() {
//     return response()->json(['user'=> auth()->guard('student')->user(), 'message'=>bcrypt('mlaadmin12345')], 200);
// })->middleware(['jwt_auth','must_be:student']);



// routes for password confirmation
Route::post('/user/confirm-password', [AuthController::class, 'confirmPassword'])
->middleware(['jwt_auth']);; //confirms authenticated user's password

// Route::post('/student/confirm-password', [AuthController::class, 'confirm_student_password'])
// ->middleware(['jwt_auth','must_be:student']);; //confirms authenticated student's password



//routes for password reset
Route::post('/send_password_reset_link', [PasswordController::class, 'send_reset_link']);
Route::post('/validate_link', [PasswordController::class, 'validate_link']);
Route::post('/reset_password', [PasswordController::class, 'reset_password']);




//routes for audit trails



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
})->middleware(['jwt_auth','must_be:admin']);



// fetches student's name based on provided email
Route::get('/admin/fetch_student_name/{email}', [StudentController::class, 'fetch_name'])->middleware(['jwt_auth','must_be:admin']);

// routes for affiliate
Route::get('/fetch-affiliate/{code}', [AffiliateController::class, 'fetch_affiliate']);#->middleware(['jwt_auth','must_be:admin']);


