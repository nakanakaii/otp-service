<?php

namespace Nakanakaii\OtpService\Tests;

use Illuminate\Support\Facades\Event;
use Nakanakaii\OtpService\Events\OtpRequested;
use Nakanakaii\OtpService\Models\OtpVerification;
use Nakanakaii\OtpService\OtpService;
use Nakanakaii\OtpService\Tests\Models\User;

class OtpServiceTest extends TestCase
{
    protected OtpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OtpService::class);
    }

    public function test_generate_creates_otp_record(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $otp = $this->service->generate($user);

        $this->assertDatabaseHas('otp_verifications', [
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => $otp->code,
            'verified' => false,
            'attempts' => 0,
        ]);
    }

    public function test_generate_returns_correct_length(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $otp = $this->service->generate($user);

        $this->assertEquals(6, strlen($otp->code));
    }

    public function test_generate_fires_event(): void
    {
        Event::fake([OtpRequested::class]);

        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $this->service->generate($user);

        Event::assertDispatched(OtpRequested::class, function (OtpRequested $event) use ($user) {
            return $event->otpable->getKey() === $user->getKey();
        });
    }

    public function test_generate_uses_config_length(): void
    {
        config(['otp.length' => 4]);

        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $otp = $this->service->generate($user);

        $this->assertEquals(4, strlen($otp->code));
    }

    public function test_verify_returns_true_for_correct_code(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);
        $otp = $this->service->generate($user);

        $result = $this->service->verify($user, $otp->code);

        $this->assertTrue($result);
    }

    public function test_verify_returns_false_for_wrong_code(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);
        $this->service->generate($user);

        $result = $this->service->verify($user, '000000');

        $this->assertFalse($result);
    }

    public function test_verify_returns_false_for_expired_otp(): void
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

        $result = $this->service->verify($user, '123456');

        $this->assertFalse($result);
    }

    public function test_verify_returns_false_when_max_attempts_exceeded(): void
    {
        config(['otp.max_attempts' => 3]);

        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        OtpVerification::create([
            'otpable_type' => User::class,
            'otpable_id' => $user->getKey(),
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'verified' => false,
            'attempts' => 3,
        ]);

        $result = $this->service->verify($user, '123456');

        $this->assertFalse($result);
    }

    public function test_verify_increments_attempts(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);
        $otp = $this->service->generate($user);

        $this->service->verify($user, '000000');

        $this->assertDatabaseHas('otp_verifications', [
            'id' => $otp->getKey(),
            'attempts' => 1,
        ]);
    }

    public function test_verify_marks_as_verified(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);
        $otp = $this->service->generate($user);

        $this->service->verify($user, $otp->code);

        $this->assertDatabaseHas('otp_verifications', [
            'id' => $otp->getKey(),
            'verified' => true,
        ]);
    }

    public function test_has_pending_returns_true_when_valid_otp_exists(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);
        $this->service->generate($user);

        $this->assertTrue($this->service->hasPending($user));
    }

    public function test_has_pending_returns_false_when_no_valid_otp(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $this->assertFalse($this->service->hasPending($user));
    }

    public function test_has_pending_returns_false_when_expired(): void
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

        $this->assertFalse($this->service->hasPending($user));
    }

    public function test_has_pending_returns_false_when_verified(): void
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

        $this->assertFalse($this->service->hasPending($user));
    }

    public function test_generate_invalidates_previous_otps(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $first = $this->service->generate($user);
        $second = $this->service->generate($user);

        $this->assertDatabaseHas('otp_verifications', [
            'id' => $first->getKey(),
            'verified' => true,
        ]);

        $this->assertDatabaseHas('otp_verifications', [
            'id' => $second->getKey(),
            'verified' => false,
        ]);
    }

    public function test_verify_does_not_increment_attempts_on_verified_otp(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);
        $otp = $this->service->generate($user);

        $this->service->verify($user, $otp->code);

        $this->service->verify($user, $otp->code);

        $otp->refresh();
        $this->assertEquals(1, $otp->attempts);
    }

    public function test_invalidate_clears_valid_otps(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $otp = $this->service->generate($user);

        $this->service->invalidate($user);

        $this->assertDatabaseHas('otp_verifications', [
            'id' => $otp->getKey(),
            'verified' => true,
        ]);

        $this->assertFalse($this->service->hasPending($user));
    }

    public function test_verify_returns_false_when_no_otp_exists(): void
    {
        $user = User::create(['name' => 'Test User', 'phone' => '1234567890']);

        $result = $this->service->verify($user, '123456');

        $this->assertFalse($result);
    }
}
