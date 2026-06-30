<?php

namespace App\Events;

use App\Models\OtpVerification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OtpRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $otpVerification;

    public function __construct(OtpVerification $otpVerification)
    {
        $this->otpVerification = $otpVerification;
    }
}

