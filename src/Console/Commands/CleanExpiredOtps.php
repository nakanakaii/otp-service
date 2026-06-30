<?php

namespace App\Console\Commands;

use App\Models\OtpVerification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanExpiredOtps extends Command
{
    protected $signature = 'otp:clean';
    
    protected $description = 'Clean up expired OTP verification records';
    
    public function handle()
    {
        $deleted = OtpVerification::where('expires_at', '<', Carbon::now())->delete();
        $this->info("Deleted {$deleted} expired OTP records.");
    }
}
