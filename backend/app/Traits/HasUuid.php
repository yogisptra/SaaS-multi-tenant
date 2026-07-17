<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * HasUuid
 *
 * Trait untuk otomatis generate UUID saat model di-create.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }
}
