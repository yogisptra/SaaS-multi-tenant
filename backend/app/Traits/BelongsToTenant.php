<?php

namespace App\Traits;

use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * BelongsToTenant
 *
 * Trait ini di-apply ke setiap Model yang memiliki kolom company_id.
 * Fungsinya:
 * 1. Menambahkan Global Scope agar setiap SELECT query otomatis di-filter WHERE company_id = ?.
 * 2. Saat creating (INSERT), otomatis mengisi company_id dari TenantService.
 *
 * Dengan trait ini, TIDAK ADA query yang bisa tembus ke tenant lain.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Global Scope: otomatis filter semua SELECT query berdasarkan company_id
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantService = app(TenantService::class);

            if ($tenantService->hasTenant()) {
                $builder->where($builder->getModel()->getTable() . '.company_id', $tenantService->getTenantId());
            }
        });

        // Observer creating: otomatis set company_id saat INSERT
        static::creating(function (Model $model) {
            $tenantService = app(TenantService::class);

            if ($tenantService->hasTenant() && !$model->company_id) {
                $model->company_id = $tenantService->getTenantId();
            }
        });
    }
}
