<?php

namespace Nakanakaii\OtpService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Nakanakaii\OtpService\Events\OtpRequested;
use Nakanakaii\OtpService\Models\OtpVerification;

class OtpService
{
    public function generate(Model $model): OtpVerification
    {
        $length = Config::get('otp.length', 6);
        $expirationMinutes = Config::get('otp.expiration_minutes', 5);

        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $otpVerification = OtpVerification::create([
            'otpable_type' => get_class($model),
            'otpable_id' => $model->getKey(),
            'code' => $code,
            'expires_at' => now()->addMinutes($expirationMinutes),
            'verified' => false,
            'attempts' => 0,
        ]);

        OtpRequested::dispatch($model, $code, $otpVerification);

        return $otpVerification;
    }

    public function verify(Model $model, string $code): bool
    {
        $maxAttempts = Config::get('otp.max_attempts', 5);

        $otpVerification = OtpVerification::query()
            ->where('otpable_type', get_class($model))
            ->where('otpable_id', $model->getKey())
            ->valid()
            ->latest()
            ->first();

        if (! $otpVerification) {
            return false;
        }

        if ($otpVerification->hasExceededMaxAttempts($maxAttempts)) {
            return false;
        }

        $otpVerification->increment('attempts');

        if ($otpVerification->code !== $code) {
            return false;
        }

        $otpVerification->update(['verified' => true]);

        return true;
    }

    public function hasPending(Model $model): bool
    {
        return OtpVerification::query()
            ->where('otpable_type', get_class($model))
            ->where('otpable_id', $model->getKey())
            ->valid()
            ->exists();
    }
}
