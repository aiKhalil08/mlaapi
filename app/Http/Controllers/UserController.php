<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\AuditTrail;
use App\Models\RoleHistory;
use App\Enums\ModelEvents;
use App\Models\UserHistory;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use App\Traits\Recaptcha;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTFactory;
use JWTAuth;
use App\Http\Requests\CreateUserRequest;

class UserController extends Controller
{
    use Recaptcha;

    /**
     * Store a newly created user in storage.
     */
    public function store(CreateUserRequest $request) {

        $validate_recaptcha = $this->validate_recaptcha();

        if ($validate_recaptcha[0] == 'failed') {
            return response()->json(['status'=> 'failed', 'message'=> $validate_recaptcha[1]], 200,);
        }

        $user = null;
        try {
            DB::transaction(function () use ($request, &$user) {

                $attributes = [...$request->only(['first_name', 'last_name', 'phone_number', 'email']), 'password'=>bcrypt($request->password)];

                $user = User::create($attributes);
                
                if (!$user) throw new \Exception('Something went wrong. Please try again.');

                // student role is assigned by all registered students by default
                $user->assignRole('student');

                $user->generateOTP();


                $user->sendWelcomeEmail();
        
                if (!$user->sendOTP('verification')) throw new \Exception('Unable to send OTP. Please try again.');
            });
            return response()->json(['status'=> 'success', 'message'=> 'OTP sent'], 200,);
        } catch (\Exception $e) {
            return response()->json(['status'=> 'failed', 'message'=> $e->getMessage()], 200,);
        }
    }

    /* verifies user email */
    public function verifyEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|size:6',
            'email' => 'required|email'
        ]);

        $input = $validator->validated();
        $user = User::where('email', $input['email'])->first();

        $status = $user->validateOTP($input['otp']);

        if ($status == 'incorrect') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input is incorrect'], 200,);
        else if ($status == 'expired') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input has expired'], 200,);

        
        if ($token = Auth::login($user)) {
            $user->email_verified = 1;
            $user->save();
            $user->refresh();
            return $this->respondWithToken($token);
        }
        return response()->json(['status'=> 'failed'], 200,);

    }

    /* sends jwt token to user */
    protected function respondWithToken($token) {

        $user = Auth::user();

        $customClaims = ['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email, 'roles'=>$user->getRoleNames()->toArray(), 'email_verified'=>$user->hasVerifiedEmail(), 'image_url'=>$user->image_url];

        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();
        
        $data = [
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
        ];

        return response()->json($data, 200);
    }

    public function delete(Request $request, string $email) {

        $user = User::where('email', $email)->first();

        if (!$user) return response()->json(['status'=>'failed', 'message'=>'No user with provided email.'], 200);

        try {
            DB::transaction(function () use ($request, &$user) {
                if ($user->image_url) $image_path = $user->image_path;
                $user->delete();
                if ($user->image_url) Storage::delete([$image_path]);
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function makeOrRevoke(Request $request, string $role_name) {
        // $role_name can either be admin or tutor
        $action = (int) $request->action; // if 0, revoke $role_name from the user. If 1, make the user $role_name
        $email = $request->email; // email of user

        $user = User::where('email', $email)->first();

        if (!$user) return response()->json(['status'=>'failed', 'message'=>'No user with provided email.'], 200);

        $articles = ['admin'=>'an', 'tutor'=>'a'];
        
        try {
            $old_roles = $user->roles->pluck('name');
            
            if ($action == 1) { // assigns $role_name role to user
                $success_message = 'User is now '.$articles[$role_name].' '. $role_name;
                $failed_message = "Unable to make user $role_name. Please try again.";
                $result = $user->assignRole($role_name);
            } else { // revokes $role_name role from user
                $success_message = 'User is no longer '.$articles[$role_name].' '. $role_name;
                $failed_message = "Unable to revoke $role_name from user. Please try again.";
                $result = $user->removeRole($role_name);
            }

            // record permissions update action in history and audit trail
            {
                /* insert old record in history table */
    
                $history_attributes = ['roles'=>json_encode($old_roles), 'user_id'=>auth()->id()];

                $history = $user->roleHistory()->save(new RoleHistory($history_attributes));
    
                /* record updated event in audit trails table */
                $action = ModelEvents::Updated;
                $object = [];
                
                $object['name'] = 'Roles';
                $object['from']['model'] = get_class($history);
                $object['from']['id'] = $history->id;
                $object['to']['model'] = get_class($user); // the 'to' object refers to the user. the present roles of the can be gotten from the User model
                $object['to']['id'] = $user->id;
    
                $attributes = [
                    'action' => $action,
                    'user_id' => auth()->id(),
                    'object' => json_encode($object)
                ];
    
                AuditTrail::create($attributes);
            
            }

            if (!$result) throw new \Exception($failed_message);
            
            return response()->json(['status'=>'success', 'message'=>$success_message], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>$th->getMessage()], 200);
        }

    }

    public function get_profile() {
        $user = auth()->user();

        $user->load('info');


        return response()->json(['profile'=>$user], 200,);
    }

    public function update_profile(Request $request) {
        $user = auth()->user();

        try {
            DB::transaction(function () use ($request, &$user) {
                $attributes = $request->only(['first_name', 'last_name', 'email', 'phone_number']);
                if (!$user->info) {
                    $no_present_info = true;
                    $user_info = new UserInfo($request->only(['home_address', 'bio']));
                } else {
                    $original_user_info = $user->info->toArray();
                    $user->info->update($request->only(['home_address', 'bio']));
                }

                if ($request->hasFile('image')) {
                    $image_name = $request->email.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/users', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                if (isset($no_present_info)) $user->info()->save($user_info);
                $user->update($attributes);

                // record update action in history and audit trail
                {
                    /* insert old record in history table */
        
                    $history_attributes = [...$user->getOriginal()];
                    if (isset($no_present_info)) $history_attributes = [...$user->getOriginal(), 'home_address'=>null, 'bio'=>null];
                    else $history_attributes = [...$user->getOriginal() , ...$original_user_info];

                    $history = $user->history()->save(new UserHistory($history_attributes));
        
                    /* record updated event in audit trails table */
                    $action = ModelEvents::Updated;
                    $object = [];
                    
                    $object['name'] = 'User';
                    $object['from']['model'] = get_class($history);
                    $object['from']['id'] = $history->id;
                    $object['to']['model'] = get_class($user);
                    $object['to']['id'] = $user->id;
        
                    $attributes = [
                        'action' => $action,
                        'user_id' => $user->id,
                        'object' => json_encode($object)
                    ];
        
                    AuditTrail::create($attributes);
                
                }
            });
        } catch (\Exception $e) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200,);
        }

        return $this->respondWithToken(auth()->getToken());
    }

    public function getAll() {
        $users = User::with('roles')
        ->get()
        ->map(function ($user) {
            $roles = array_map(function($role) {
                return $role['name'];
            } ,$user->roles->toArray());

            return ['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email, 'roles'=>$roles];
        });

        return response()->json(['users'=>$users], 200);
    }

    public function get(Request $request, string $email) {
        $user = User::where('email', $email)->with('info', 'roles:name')->first();

        if (!$user) return response()->json(['status'=> 'failed', 'message'=> 'user with provided email not found'], 200);

        return response()->json(['status'=>'success', 'user'=>$user], 200,);
    }
}