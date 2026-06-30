<?php

namespace Nakanakaii\OtpService\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OtpVerification extends Model
{
    protected $fillable = [
        'otpable_type',
        'otpable_id',
        'code',
        'expires_at',
        'verified',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
        'attempts' => 'integer',
    ];

    public function otpable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeValid($query)
    {
        return $query->where('verified', false)
            ->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasExceededMaxAttempts(int $maxAttempts): bool
    {
        return $this->attempts >= $maxAttempts;
    }
}
