<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PasswordController extends Controller
{
    private function generate_token() {
        $array = [rand(0,9), rand(0,9), rand(0,9), rand(0,9), rand(0,9), rand(0,9)];
        return implode('', $array);
    }

    private function remove_token($email) {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    private function fetch_token($email) {

        $token = DB::table('password_reset_tokens')->where('email', $email)->first();
        return $token;
    }


    private function insert_token_in_db($email, $token) {
        $now = \Carbon\Carbon::now();
        $expires_at = \Carbon\Carbon::now()->addMinutes(30);


        if (DB::table('password_reset_tokens')->upsert([
            'email' => $email,
            'token' => $token,
            'created_at' => $now,
            'expires_at' => $expires_at
        ], ['email'], ['token', 'created_at', 'expires_at'])) return true;

        return false;
    }

    private function create_reset_link($email, $token) {
        $signature = base64_encode($email.':'.$token);
        // return 'https://mla.mitiget.com/reset-password?s='.$signature;
        return 'http://localhost:4200/reset-password?s='.$signature;
    }

    private function compose_mail($first_name, $email, $reset_link) {
        return [
            'title' => 'Reset Your Password for MLA',

            'message' => view('emails.forgot-password', ['first_name'=>$first_name, 'link'=>$reset_link])->render(),
            
            // 'message' => '<p>Follow this link to reset your password: <a href=\''.$reset_link.'\'>Click here</a>.<p>Link is only valid for 30 minutes.',
            
            'email' => $email,
            
            'companyemail' => env('COMPANY_EMAIL'),
            
            'companypassword' => env('COMPANY_PASSWORD'),
        ];
    }

    public function send_reset_link(Request $request) {
        $user = null;
        $email = $request->email;
        $user = User::where('email',$email)->first();
        
        if (!$user) return response()->json(['status'=>'failed', 'message'=>'Invalid email address'], 200);


        try {
            DB::transaction(function () use ($user, $email) {
                $api_endpoint = 'https://mitiget.com.ng/mailerapi/message/singlemail';
                $token = $this->generate_token();
                $reset_link = $this->create_reset_link($email, $token);
                $data = $this->compose_mail($user->first_name, $email, $reset_link);
                $response = \Illuminate\Support\Facades\Http::post($api_endpoint, $data);
                $this->insert_token_in_db(token: $token, email: $email);
                if (!$response->ok()) {
                    throw new \Exception('couldn\'t send email');
                }
            });
            return response()->json(['status'=>'success', 'message'=>'Password reset link sent'], 200);
        } catch (\Exception $e) {
            return response()->json(['status'=>'failed', 'message'=>'Couldn\'t send reset link'], 200);
        }

    }

    public function validate_link(Request $request) {
        $token = $this->fetch_token($request->email);


        if (!$token) return response()->json(['status'=>'failed', 'message'=>'Token does not exist'], 200);

        if ($token->token != $request->token) return response()->json(['status'=>'failed', 'message'=>'Incorrect token'], 200);

        if (\Carbon\Carbon::now() > $token->expires_at) return response()->json(['status'=>'failed', 'message'=>'Expired link'], 200);

        return response()->json(['status'=>'success', 'message'=>'Valid link'], 200);
    }


    public function reset_password(Request $request) {
        $email = $request->email;

        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        $input = $validator->validated();

        $user = null;

        $user = User::where('email', $email)->first();
        

        if ($user) {
            $user->update(['password'=>bcrypt($input['password'])]);
            $this->remove_token($email);
            return response()->json(['status'=>'success', 'message'=>'Password reset'], 200);
        }

        return response()->json(['status'=>'failed', 'message'=>'Couldn\'t reset password'], 200);
    }

}
