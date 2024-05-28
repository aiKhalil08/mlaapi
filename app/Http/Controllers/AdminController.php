<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\AuditTrail;
use App\Models\PermissionHistory;
use App\Enums\ModelEvents;
use Illuminate\Http\Request;
use App\Http\Requests\AddAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    // public function store(AddAdminRequest $request)
    // {
    //     $admin = null;
    //     try {
    //         DB::transaction(function () use ($request, &$admin) {

    //             $attributes = [...$request->only(['first_name', 'last_name', 'phone_number', 'email']), 'password'=>bcrypt($request->password)];

    //             if ($request->hasFile('image')) {
    //                 $image_name = $request->email.'.'.$request->image->extension();
    //                 $image_url = $request->image->storeAs('images/admins', $image_name);
    //                 $attributes = [...$attributes, 'image_url'=>$image_url];
    //             }
    //             $admin = Admin::create($attributes);
                
    //             if (!$admin) throw new \Exception('Something went wrong. Please try again.');
    //         });
    //         return response()->json(['status'=> 'success', 'message'=> 'Admin has been added'], 200,);
    //     } catch (\Exception $e) {
    //         return response()->json(['status'=> 'failed', 'message'=> $e->getMessage()], 200,);
    //     }
    // }

    /**
     * Display the specified resource.
     */
    public function get(string $email)
    {
        $admin = User::areAdmins()->where('email', $email)->with('permissions')->first();
        // var_dump($admin->toArray()); return null;

        if (!$admin) return response()->json(['status'=> 'failed', 'message'=> 'Admin with provided email not found'], 200);

        return response()->json(['status'=>'success', 'admin'=>$admin], 200,);
    }


    public function get_all() {
        return response()->json(['admins'=>User::areAdmins()->select(['first_name', 'last_name', 'email'])->get()], 200);
    }

    public function getPermissions(string $email) {
        $admin = User::areAdmins()->where('email', $email)->with('permissions')->first();

        if (!$admin) return response()->json(['status'=> 'failed', 'message'=> 'Admin with provided email not found'], 200);

        $all_permissions = Permission::all();
        $admin_permissions = $admin->permissions()->pluck('id')->toArray();

        foreach ($all_permissions as $permission) {
            $permission->admin_has = in_array($permission->id, $admin_permissions);
        }

        return response()->json(['status'=>'success', 'permissions'=>$all_permissions->makeVisible('id')], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $email)
    {
        $admin = User::areAdmins()->where('email', $email)->first();

        if (!$admin) return response()->json(['status'=> 'failed', 'message'=> 'Admin with provided email not found'], 200);

        Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', Rule::unique('admins')->ignore($admin->id)],
            'phone_number' => ['required', Rule::unique('admins')->ignore($admin->id)],
            'password' => ['sometimes', 'required', 'confirmed'],
            'password_confirmation' => ['required_with:password']
        ])->validate();

        try {
            DB::transaction(function () use ($request, &$admin) {
                $attributes = $request->only(['first_name', 'last_name', 'email', 'phone_number']);
                if ($request->password) $attributes = [...$attributes, 'password'=>bcrypt($request->password)];
                if ($request->hasFile('image')) {
                    $image_name = $request->email.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/admins', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                $admin->update($attributes);
            });
            return response()->json(['status'=> 'success', 'message'=> 'Admin has been updated'], 200,);
        } catch (\Exception $e) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200,);
        }
    }

    public function updatePermissions(Request $request, string $email) {

        $admin = User::areAdmins()->where('email', $email)->with('permissions')->first();

        if (!$admin) return response()->json(['status'=> 'failed', 'message'=> 'Admin with provided email not found'], 200);

        try {
            DB::transaction(function () use ($request, $admin) {
                $permissions = $request->permissions ? array_keys($request->permissions) : null;
                $old_permissions = $admin->getAllPermissions()->pluck('name');
                $admin->permissions()->sync($permissions);

                // record permissions update action in history and audit trail
                {
                    /* insert old record in history table */
        
                    $history_attributes = ['permissions'=>json_encode($old_permissions), 'user_id'=>auth()->id()];

                    $history = $admin->permissionHistory()->save(new PermissionHistory($history_attributes));
        
                    /* record updated event in audit trails table */
                    $action = ModelEvents::Updated;
                    $object = [];
                    
                    $object['name'] = 'Permissions';
                    $object['from']['model'] = get_class($history);
                    $object['from']['id'] = $history->id;
                    $object['to']['model'] = get_class($admin); // the 'to' object refers to the user. the present permissions of the can be gotten from the User model
                    $object['to']['id'] = $admin->id;
        
                    $attributes = [
                        'action' => $action,
                        'user_id' => auth()->id(),
                        'object' => json_encode($object)
                    ];
        
                    AuditTrail::create($attributes);
                
                }
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function delete(Request $request, string $email) {

        try {
            
            $admin = Admin::where('email', $email)->first();
            DB::transaction(function () use ($request, &$admin) {
                if ($admin->image_url) $image_path = $admin->image_path;
                $admin->delete();
                if ($admin->image_url) Storage::delete([$image_path]);
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }
}
