<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantMiddleware
 *
 * Middleware ini dijalankan SETELAH auth:api.
 * Fungsinya mengambil company_id dari user yang login (JWT)
 * dan menyimpannya ke TenantService singleton.
 *
 * Semua query setelah middleware ini akan otomatis ter-filter
 * oleh Global Scope di trait BelongsToTenant.
 */
class TenantMiddleware
{
    public function __construct(private TenantService $tenantService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if ($user && $user->company_id) {
            $this->tenantService->setTenant($user->company_id);
        }

        return $next($request);
    }
}
