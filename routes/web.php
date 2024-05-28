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

Route::get('/create-permissions-roles', function () {
    // $actions =['show_all', 'show_single', 'add', 'update', 'delete'];
    // $models = ['course', 'blog', 'event', 'admin', 'testimonial', 'certificate'];
    // $count = 0;

    $permissions = ['all', 'courses', 'events', 'resources', 'requests', 'students', 'cohorts', 'certificates', 'sales', 'affiliates', 'admins', 'fulfillments', 'audit'];

    $roles = ['admin', 'student', 'tutor', 'external_user'];

    \Spatie\Permission\Models\Permission::insert(array_map(fn ($permission) => ['name' => 'crud '.$permission, 'guard_name'=>'user-jwt'], $permissions));

    \Spatie\Permission\Models\Role::insert(array_map(fn ($role) => ['name' => $role, 'guard_name'=>'user-jwt'], $roles));

    // for ($i=1; $i <= count($permissions) ; $i++) { 
    //     \Illuminate\Support\Facades\DB::insert('insert into privileges set id = ?, name = ?', [$i, $permissions[$i - 1]]);
    // }

    // foreach ($actions as $action) {
    //     foreach ($models as $model) {
    //         $privilege = $action.'-'.$model;
    //         \Illuminate\Support\Facades\DB::insert('insert into privileges set name = ?', [$privilege]);
    //         $count++;
    //     }
    // }

    // echo $count.' privileges added';
});

Route::view('/first_view', 'first-view')->name('first_view');
Route::view('/second_view', 'second-view')->name('second_view');