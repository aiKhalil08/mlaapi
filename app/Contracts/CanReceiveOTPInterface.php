<?php

namespace App\Contracts;

interface CanReceiveOTPInterface {

    public function otp();

    public function generateOTP();

    public function sendOTP(string $type): bool;

    public function validateOTP(string $user_input): string;
}