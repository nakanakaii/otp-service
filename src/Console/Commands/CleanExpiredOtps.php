<?php

namespace Nakanakaii\OtpService\Console\Commands;

use Illuminate\Console\Command;
use Nakanakaii\OtpService\Models\OtpVerification;

class CleanExpiredOtps extends Command
{
    protected $signature = 'otp:clean';

    protected $description = 'Delete expired OTP verification records';

    public function handle(): int
    {
        $deleted = OtpVerification::where('expires_at', '<', now())->delete();

        $this->info("Deleted {$deleted} expired OTP records.");

        return self::SUCCESS;
    }
}
