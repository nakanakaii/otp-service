<?php

namespace Nakanakaii\OtpService\Tests;

use Nakanakaii\OtpService\OtpFacade;
use Nakanakaii\OtpService\OtpService;

class OtpServiceProviderTest extends TestCase
{
    public function test_config_is_mergeable(): void
    {
        $this->assertArrayHasKey('length', config('otp'));
        $this->assertArrayHasKey('expiration_minutes', config('otp'));
        $this->assertArrayHasKey('max_attempts', config('otp'));
    }

    public function test_otp_service_is_singleton(): void
    {
        $first = app(OtpService::class);
        $second = app(OtpService::class);

        $this->assertSame($first, $second);
    }

    public function test_otp_facade_resolves_to_service(): void
    {
        $user = \Nakanakaii\OtpService\Tests\Models\User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $otp = OtpFacade::generate($user);

        $this->assertInstanceOf(\Nakanakaii\OtpService\Models\OtpVerification::class, $otp);
    }
}
