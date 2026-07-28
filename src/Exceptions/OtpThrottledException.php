<?php

namespace Nakanakaii\OtpService\Exceptions;

use Exception;
use Throwable;

class OtpThrottledException extends Exception
{
    public function __construct(
        public readonly int $secondsUntilAvailable,
        ?string $message = null,
        int $code = 429,
        ?Throwable $previous = null,
    ) {
        $message ??= "Too many OTP requests. Try again in {$secondsUntilAvailable} seconds.";

        parent::__construct($message, $code, $previous);
    }

    public function getSecondsUntilAvailable(): int
    {
        return $this->secondsUntilAvailable;
    }
}
