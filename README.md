# nakanakaii/otp-service

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nakanakaii/otp-service)](https://packagist.org/packages/nakanakaii/otp-service)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A reusable Laravel package for OTP verification with polymorphic model support and event-driven delivery. Any model (User, Order, Device, etc.) can use OTP via polymorphic relations. Consumers write their own listener to send OTP via their preferred channel (SMS, WhatsApp, email, etc.).

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

## Installation

```bash
composer require nakanakaii/otp-service
```

Publish the config and migration:

```bash
php artisan vendor:publish --tag=otp-config
php artisan vendor:publish --tag=otp-migrations
```

Run the migration:

```bash
php artisan migrate
```

## Setup

### 1. Write a Listener

The package fires an `OtpRequested` event when an OTP is generated. Create a listener to send it via your preferred channel:

```php
<?php

namespace App\Listeners;

use App\Models\User;
use Nakanakaii\OtpService\Events\OtpRequested;

class SendOtpNotification
{
    public function handle(OtpRequested $event): void
    {
        /** @var User $user */
        $user = $event->otpable;
        $code = $event->code;

        // Send via your preferred channel
        $user->notify(new OtpNotification($code));
    }
}
```

### 2. Register the Listener

In your `EventServiceProvider`:

```php
use Nakanakaii\OtpService\Events\OtpRequested;
use App\Listeners\SendOtpNotification;

protected $listen = [
    OtpRequested::class => [
        SendOtpNotification::class,
    ],
];
```

### 3. Schedule Cleanup

Add to `routes/console.php` (Laravel 10/11) or `Console/Kernel.php` (Laravel 10):

```php
// Laravel 11+
use Illuminate\Support\Facades\Schedule;

Schedule::command('otp:clean')->daily();
```

Or run manually:

```bash
php artisan otp:clean
```

## Usage

### Generate an OTP

```php
use App\Models\User;
use Nakanakaii\OtpService\Facades\Otp;

$user = User::find(1);
$otp = Otp::generate($user);

// $otp->code — the generated code (e.g. "084721")
// $otp->expires_at — expiration datetime
```

### Verify an OTP

```php
$verified = Otp::verify($user, '084721');

if ($verified) {
    // Code is valid
} else {
    // Invalid or expired code, or max attempts exceeded
}
```

### Check for Pending OTP

```php
if (Otp::hasPending($user)) {
    // Model has an unexpired, unverified OTP
}
```

## Configuration

```php
// config/otp.php
return [
    'length'              => env('OTP_LENGTH', 6),
    'expiration_minutes'  => env('OTP_EXPIRATION_MINUTES', 5),
    'max_attempts'        => env('OTP_MAX_ATTEMPTS', 5),
];
```

| Option | Default | Description |
|---|---|---|
| `length` | `6` | Number of digits in the OTP code |
| `expiration_minutes` | `5` | Minutes until an OTP expires |
| `max_attempts` | `5` | Verification attempts before invalidation |

## Database

The `otp_verifications` table structure:

| Column | Type | Description |
|---|---|---|
| `id` | `bigint` | Primary key |
| `otpable_type` | `string` | Polymorphic model class |
| `otpable_id` | `unsignedBigInteger` | Polymorphic model ID |
| `code` | `string` | The OTP code |
| `expires_at` | `datetime` | Expiration timestamp |
| `verified` | `boolean` | Whether the code was verified |
| `attempts` | `integer` | Number of verification attempts |
| `created_at` | `timestamp` | Created timestamp |
| `updated_at` | `timestamp` | Updated timestamp |

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
