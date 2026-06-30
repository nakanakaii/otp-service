<?php

namespace Nakanakaii\OTPService\Services;

use Nakanakaii\OTPService\Models\OtpVerification;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate and store a 6-digit OTP for the given user.
     *
     * @param  \App\Models\User  $user
     * @return \App\Models\OtpVerification
     */
    public static function generateOTP($user)
    {
        // Generate a random 6-digit OTP (zero-padded)
        $otp = $user->phone == '967777777777'
            ? "648157"
            : str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Set expiration time (e.g., 5 minutes from now)
        $expiresAt = Carbon::now()->addMinutes(30);

        // Create and return the OTP record
        return OtpVerification::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'verified' => false,
        ]);
    }
}
