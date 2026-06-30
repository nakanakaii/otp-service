<?php

namespace Nakanakaii\OtpService;

use Illuminate\Support\ServiceProvider;

class OtpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OtpService::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/otp.php' => config_path('otp.php'),
        ], 'otp-config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/otp.php', 'otp'
        );

        $this->publishes([
            __DIR__ . '/../database/migrations/create_otp_verifications_table.php' => $this->getMigrationFileName(),
        ], 'otp-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\CleanExpiredOtps::class,
            ]);
        }
    }

    protected function getMigrationFileName(): string
    {
        $timestamp = now()->format('Y_m_d_His');

        return database_path("migrations/{$timestamp}_create_otp_verifications_table.php");
    }
}
