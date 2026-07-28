<?php

namespace Nakanakaii\OtpService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Nakanakaii\OtpService\Events\OtpRequested;
use Nakanakaii\OtpService\Exceptions\OtpThrottledException;
use Nakanakaii\OtpService\Models\OtpVerification;

class OtpService
{
    public function generate(Model $model, ?string $identifier = null): OtpVerification
    {
        $this->checkRateLimit($model, $identifier);

        $this->invalidate($model);

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

        $this->hitRateLimit($model, $identifier);

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

    public function invalidate(Model $model): void
    {
        OtpVerification::query()
            ->where('otpable_type', get_class($model))
            ->where('otpable_id', $model->getKey())
            ->valid()
            ->update(['verified' => true]);
    }

    public function hasPending(Model $model): bool
    {
        return OtpVerification::query()
            ->where('otpable_type', get_class($model))
            ->where('otpable_id', $model->getKey())
            ->valid()
            ->exists();
    }

    public function availableIn(Model $model, ?string $identifier = null): int
    {
        $key = $this->rateLimitKey($model, $identifier);

        if (! RateLimiter::tooManyAttempts($key, 1)) {
            return 0;
        }

        return RateLimiter::availableIn($key);
    }

    protected function checkRateLimit(Model $model, ?string $identifier): void
    {
        if (! Config::get('otp.rate_limit.enabled', false)) {
            return;
        }

        $key = $this->rateLimitKey($model, $identifier);
        $maxAttempts = Config::get('otp.rate_limit.max_attempts', 3);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw new OtpThrottledException(
                secondsUntilAvailable: max(0, $seconds),
            );
        }
    }

    protected function hitRateLimit(Model $model, ?string $identifier): void
    {
        if (! Config::get('otp.rate_limit.enabled', false)) {
            return;
        }

        $key = $this->rateLimitKey($model, $identifier);
        $decayMinutes = Config::get('otp.rate_limit.decay_minutes', 1);

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    protected function rateLimitKey(Model $model, ?string $identifier): string
    {
        if ($identifier) {
            return 'otp:' . $identifier;
        }

        return 'otp:' . $model->getMorphClass() . ':' . $model->getKey();
    }
}
