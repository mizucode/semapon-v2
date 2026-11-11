<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\StoreMembership;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Allow store creation and logout routes without an active tenant
        if ($request->routeIs('stores.create', 'logout', 'profile')) {
            return $next($request);
        }

        $activeStoreId = session('active_store_id');
        $membership = null;

        if ($activeStoreId) {
            $membership = StoreMembership::with('store')
                ->where('user_id', $user->id)
                ->where('store_id', $activeStoreId)
                ->where('status', 'active')
                ->first();
        }

        if (! $membership) {
            $membership = StoreMembership::with('store')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($membership) {
                session(['active_store_id' => $membership->store_id]);
            }
        }

        if (! $membership || ! $membership->store) {
            // User does not belong to any active store, redirect to store onboarding
            return redirect()->route('stores.create');
        }

        $store = $membership->store;

        if ($store->status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors([
                'email' => 'Toko Anda saat ini sedang ditangguhkan. Silakan hubungi dukungan SEMAPON.',
            ]);
        }

        $this->tenantContext->setStore($store, $membership);

        View::share('activeStore', $store);
        View::share('activeRole', $membership->role);

        return $next($request);
    }
}
