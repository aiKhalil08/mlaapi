<?php

namespace App\Traits;


trait CanReceiveOTPTrait {

    private string $generated_otp;

    public function generate_otp() {
        $array = [rand(0,9), rand(0,9), rand(0,9), rand(0,9), rand(0,9), rand(0,9)];

        $this->generated_otp = implode('', $array);
    }

    public function send_otp(string $type): bool {

        $status = null;

        
        $api_endpoint = 'https://mitiget.com.ng/mailerapi/message/singlemail';

        if ($type == 'verification') {
            $title = 'Email Verification OTP';
            $message = '<p>Verify your email with this one time passcode: <b>'.$this->generated_otp.'</b>. Code is valid for 30 minutes.</p>';
        } else {
            $title = 'Sign in OTP';
            $message = '<p>Sign in to your Mitget Learning Academy account with this one time passcode: <b>'.$this->generated_otp.'</b>. Code is valid for 30 minutes.</p>';
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
                // var_dump('here? no!',$response->body()); return null;
                if (!$response->ok()) {
                    throw new \Exception('couldn\'t send email');
                }
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

    public function validate_otp(string $user_input): string {
        $status = null;
        if ($user_input != $this->otp->code) $status = 'incorrect';
        else if ($user_input == $this->otp->code && $this->otp->isExpired()) $status = 'expired';
        else $status = 'valid';

        if ($status == 'valid') $this->otp->delete();
        return $status;
     }

    public function otp(): \Illuminate\Database\Eloquent\Relations\MorphOne {
        return $this->morphOne(\App\Models\OTP::class, 'owner');
    }
}   