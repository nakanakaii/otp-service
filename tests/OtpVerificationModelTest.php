<?php

namespace Nakanakaii\OtpService\Tests;

use Nakanakaii\OtpService\Models\OtpVerification;
use Nakanakaii\OtpService\OtpService;
use Nakanakaii\OtpService\Tests\Models\User;

class OtpVerificationModelTest extends TestCase
{
    protected OtpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OtpService::class);
    }

    public function test_otpable_relation(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);
        $otp = $this->service->generate($user);

        $this->assertTrue($otp->otpable->is($user));
    }

    public function test_scope_valid_excludes_verified(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'verified' => true,
            'attempts' => 1,
        ]);

        $validOtps = OtpVerification::query()->valid()->get();

        $this->assertCount(0, $validOtps);
    }

    public function test_scope_valid_excludes_expired(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '123456',
            'expires_at' => now()->subMinutes(10),
            'verified' => false,
            'attempts' => 0,
        ]);

        $validOtps = OtpVerification::query()->valid()->get();

        $this->assertCount(0, $validOtps);
    }

    public function test_scope_valid_includes_unverified_not_expired(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'verified' => false,
            'attempts' => 0,
        ]);

        $validOtps = OtpVerification::query()->valid()->get();

        $this->assertCount(1, $validOtps);
    }

    public function test_is_expired_returns_true_when_expired(): void
    {
        $otp = OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => 1,
            'code' => '123456',
            'expires_at' => now()->subMinutes(10),
            'verified' => false,
            'attempts' => 0,
        ]);

        $this->assertTrue($otp->isExpired());
    }

    public function test_is_expired_returns_false_when_not_expired(): void
    {
        $otp = OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => 1,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'verified' => false,
            'attempts' => 0,
        ]);

        $this->assertFalse($otp->isExpired());
    }

    public function test_has_exceeded_max_attempts(): void
    {
        $otp = OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => 1,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'verified' => false,
            'attempts' => 5,
        ]);

        $this->assertTrue($otp->hasExceededMaxAttempts(5));
        $this->assertFalse($otp->hasExceededMaxAttempts(6));

        $otp2 = OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => 1,
            'code' => '654321',
            'expires_at' => now()->addMinutes(5),
            'verified' => false,
            'attempts' => 2,
        ]);

        $this->assertFalse($otp2->hasExceededMaxAttempts(5));
    }
}
