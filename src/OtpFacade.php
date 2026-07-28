<?php

namespace Nakanakaii\OtpService;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Nakanakaii\OtpService\Models\OtpVerification generate(\Illuminate\Database\Eloquent\Model $model, ?string $identifier = null)
 * @method static bool verify(\Illuminate\Database\Eloquent\Model $model, string $code)
 * @method static bool hasPending(\Illuminate\Database\Eloquent\Model $model)
 * @method static int availableIn(\Illuminate\Database\Eloquent\Model $model, ?string $identifier = null)
 *
 * @see \Nakanakaii\OtpService\OtpService
 */
class OtpFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OtpService::class;
    }
}
