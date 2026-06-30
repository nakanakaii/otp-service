<?php

namespace Nakanakaii\OtpService\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nakanakaii\OtpService\Models\OtpVerification;

class OtpRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Model $otpable,
        public readonly string $code,
        public readonly OtpVerification $otpVerification,
    ) {}
}
