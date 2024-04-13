<?php

namespace App\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;



trait Recaptcha {

    public function validate_recaptcha() {
        try {

            // var_dump(request()->input('g-recaptcha-response')); return null;
            if (!$token_response = request()->input('g-recaptcha-response')) return ['failed', 'Invalid recaptcha.'];

            $api_endpoint = 'https://www.google.com/recaptcha/api/siteverify';

            // var_dump(env('RECAPTCHA_SECRET')); return null;
            $data = [
                'secret' => env('RECAPTCHA_SECRET'),
                'response' => $token_response,
            ];

            $response = Http::withQueryParameters($data)->post($api_endpoint);

            if (!$response->ok()) return ['failed', 'Something went wrong. Please try again.'];

            $json = $response->json();

            // var_dump($json); return null;


            if ($json['success'] != true) return ['failed', 'Unable to validate recaptcha.'];

            return ['success'];


        } catch (\Exception $e) {
            return ['failed', 'Something went wrong. Please try again.'];
        }
    }
}   