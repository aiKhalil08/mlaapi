<?php

namespace App\Contracts;

interface CanReceiveOTP {

    public function otp();

    public function generate_otp();

    public function send_otp(string $type): bool;

    public function validate_otp(string $user_input): string;
}