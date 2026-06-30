<?php

namespace Nakanakaii\OtpService\Tests;

use Nakanakaii\OtpService\Models\OtpVerification;
use Nakanakaii\OtpService\Tests\Models\User;

class CleanExpiredOtpsTest extends TestCase
{
    public function test_command_deletes_expired_otps(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '111111',
            'expires_at' => now()->subMinutes(10),
            'verified' => false,
            'attempts' => 0,
        ]);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '222222',
            'expires_at' => now()->subMinutes(5),
            'verified' => false,
            'attempts' => 0,
        ]);

        $this->artisan('otp:clean');

        $this->assertDatabaseCount('otp_verifications', 0);
    }

    public function test_command_keeps_valid_otps(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '111111',
            'expires_at' => now()->addMinutes(5),
            'verified' => false,
            'attempts' => 0,
        ]);

        $this->artisan('otp:clean');

        $this->assertDatabaseCount('otp_verifications', 1);
    }

    public function test_command_output_shows_count(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '111111',
            'expires_at' => now()->subMinutes(10),
            'verified' => false,
            'attempts' => 0,
        ]);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '222222',
            'expires_at' => now()->addMinutes(5),
            'verified' => false,
            'attempts' => 0,
        ]);

        $this->artisan('otp:clean')
            ->expectsOutput('Deleted 1 expired OTP records.')
            ->assertExitCode(0);
    }
}
