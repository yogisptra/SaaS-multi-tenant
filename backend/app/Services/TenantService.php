<?php

namespace App\Services;

/**
 * TenantService
 *
 * Singleton service yang menyimpan konteks tenant (company_id) saat ini.
 * Diset oleh TenantMiddleware dari JWT user yang login.
 * Digunakan oleh BelongsToTenant trait untuk auto-filter query.
 */
class TenantService
{
    private ?int $tenantId = null;

    public function setTenant(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function hasTenant(): bool
    {
        return $this->tenantId !== null;
    }
}
