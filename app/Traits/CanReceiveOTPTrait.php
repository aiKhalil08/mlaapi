<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\OTP;


trait CanReceiveOTPTrait {

    private string $generated_otp;

    public function generateOTP() {
        $array = [rand(0,9), rand(0,9), rand(0,9), rand(0,9), rand(0,9), rand(0,9)];

        $this->generated_otp = implode('', $array);
    }

    public function sendOTP(string $type): bool {

        $status = null;

        
        $api_endpoint = 'https://mitiget.com.ng/mailerapi/message/singlemail';
        

        if ($type == 'verification') {
            $title = 'Verify Your Email Address and Unlock Mitiget Learning!';
            $message = view('emails.verify', ['first_name'=>$this->first_name, 'otp_code'=>$this->generated_otp])->render();
        } else {
            $title = 'Secure Login to Mitiget Learning Academy (One-Time Code)';
            $message = view('emails.login', ['first_name'=>$this->first_name, 'otp_code'=>$this->generated_otp])->render();
        }
        
        $data = [
            'title' => $title,
            
            'message' => $message,
            
            'email' => $this->email,
            
            'companyemail' => env('COMPANY_EMAIL'),
            
            'companypassword' => env('COMPANY_PASSWORD'),
        ];

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($api_endpoint, $data, &$status) {
                // var_dump('did you get here? yes');
                $response = \Illuminate\Support\Facades\Http::post($api_endpoint, $data);
                if ($this->otp) $this->otp()->delete();
                $now = \Carbon\Carbon::now();
                $this->otp()->create(['code'=>$this->generated_otp, 'expires_at'=>$now->addMinutes(30)]);
                // return response()->json(['body'=>$response->body()], 200);
                // var_dump('here? no!', ($response->body())); return null;
                // if (!$response->ok()) {
                //     throw new \Exception('couldn\'t send email');
                // }
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

    public function validateOTP(string $user_input): string {
        $status = null;
        if ($user_input != $this->otp->code) $status = 'incorrect';
        else if ($user_input == $this->otp->code && $this->otp->isExpired()) $status = 'expired';
        else $status = 'valid';

        if ($status == 'valid') $this->otp->delete();
        return $status;
     }

    public function otp(): HasOne {
        return $this->hasOne(OTP::class);
    }
}   