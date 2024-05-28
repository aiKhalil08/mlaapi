<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ExternalUser;
use App\Models\UnhashedPassword;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExternalUserController extends Controller
{
    public function store(Request $request) {
        $user = null;
        try {
            DB::transaction(function () use ($request, &$user) {

                $attributes = [...$request->only(['first_name', 'last_name', 'email'])];
                $unhashed_password = new UnhashedPassword;

                if ($request->password) {
                    $attributes = [...$attributes, 'password'=>bcrypt($request->password)];
                    $unhashed_password->password = $request->password;
                }
                
                
                $user = User::create($attributes);
                
                if (!$user) throw new \Exception('Something went wrong. Please try again.');

                if (!$request->password) {
                    $generated_password = '@'.\strtolower(\str_replace(' ', '', $request->company)).'#'.\str_pad($user->id, 4, '0', STR_PAD_LEFT);
                    
                    $user->update(['password'=>bcrypt($generated_password)]);
                    $unhashed_password->password = $generated_password;
                }

                //assign external_role role to user
                $user->assignRole('external_user');

                $external_user = new ExternalUser($user->makeVisible('id')->toArray()); // cast $user into ExternalUser

                // store external user's unhashed password which is usually included in quiz assignment notification emails
                $external_user->unhashedPassword()->save($unhashed_password);

                // attach external user to a company
                $company = Company::where('name', $request->company)->first();

                if (!$company) throw new \Exception('Company does not exist.');

                $external_user->companies()->attach($company);
                
            });
            return response()->json(['status'=> 'success', 'message'=> 'External user has been added'], 200,);
        } catch (\Exception $e) {
            return response()->json(['status'=> 'failed', 'message'=> $e->getMessage()], 200,);
        }
    }
    
    public function update(Request $request, string $email)
    {
        $user = User::areExternalUsers()->where('email', $email)->first();

        if (!$user) return response()->json(['status'=> 'failed', 'message'=> 'User with provided email not found'], 200);

        Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'company' => ['required'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'confirmed'],
            'password_confirmation' => ['required_with:password']
        ])->validate();

        $external_user = new ExternalUser($user->makeVisible('id')->toArray()); // cast $user into ExternalUser

        try {
            DB::transaction(function () use ($request, &$user, &$external_user) {
                $attributes = $request->only(['first_name', 'last_name', 'email']);
                if ($request->password) {
                    $attributes = [...$attributes, 'password'=>bcrypt($request->password)];
                }
                $user->update($attributes);


                // check if inputted company name is different from present one and change accordingly
                if ($external_user->company->name != $request->company) {
                    // attach external user to a company
                    $company = Company::where('name', $request->company)->first();
    
                    if (!$company) throw new \Exception('Company does not exist.');
    
                    $external_user->companies()->sync([$company->id]);
                }

                // check if inputted password is different from present one and change accordingly
                if ($request->password && $external_user->unhashedPassword->password != $request->password) {
                    // store external user's unhashed password which is usually included in quiz assignment notification emails
                    $external_user->unhashedPassword->update(['password'=>$request->password]);
                }
                
            });
            return response()->json(['status'=> 'success', 'message'=> 'User has been updated'], 200,);
        } catch (\Exception $e) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200,);
        }
    }

    public function delete(Request $request, string $email) {

        try {
            
            $user = User::areExternalUsers()->where('email', $email)->first();
            DB::transaction(function () use ($request, &$user) {
                $user->delete();
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function getAll() {
        $users = ExternalUser::areExternalUsers()->with('companies')->get()->map(function ($user) {
            return ['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email, 'company'=>$user->company];
        });

        return response()->json(['users'=>$users], 200);
    }

    public function get(Request $request, string $email) {
        $user = ExternalUser::areExternalUsers()->where('email', $email)->with('companies')->first();

        if (!$user) return response()->json(['status'=> 'failed', 'message'=> 'User with provided email not found'], 200);

        // $user->load('')

        // var_dump($user->companies); return null;

        return response()->json(['status'=>'success', 'user'=>['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email, 'company'=>$user->company]], 200,);
    }

}
