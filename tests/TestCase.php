<?php

namespace Nakanakaii\OtpService\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Nakanakaii\OtpService\OtpServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [OtpServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('cache.default', 'array');
    }

    protected function setUpDatabase(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('otpable');
            $table->string('code');
            $table->dateTime('expires_at');
            $table->boolean('verified')->default(false);
            $table->integer('attempts')->default(0);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }
}
