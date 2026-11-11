<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if ($request->user() && in_array('super_admin', $roles, true) && $request->user()->isSuperAdmin()) {
            return $next($request);
        }

        $currentRole = $this->tenantContext->getRole();

        if (! $currentRole || ! in_array($currentRole, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
