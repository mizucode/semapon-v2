<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\ServiceTrackingToken;
use Illuminate\Support\Facades\DB;

class TrackingTokenService
{
    /**
     * Generate a new 256-bit secure tracking token for a service order.
     *
     * @return array{plain_token: string, token: ServiceTrackingToken}
     */
    public function generateToken(ServiceOrder $order, int $validDays = 90): array
    {
        // 256-bit secure random token (32 bytes = 64 hex characters)
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $tokenRecord = ServiceTrackingToken::create([
            'store_id' => $order->store_id,
            'service_order_id' => $order->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays($validDays),
        ]);

        return [
            'plain_token' => $plainToken,
            'token' => $tokenRecord,
        ];
    }

    /**
     * Resolve service order from a plain tracking token.
     * Updates last_accessed_at and last_accessed_ip for audit trail.
     */
    public function resolveOrder(string $plainToken, ?string $clientIp = null): ?ServiceOrder
    {
        if (empty(trim($plainToken))) {
            return null;
        }

        $tokenHash = hash('sha256', trim($plainToken));

        // Use withoutGlobalScopes or tenant bypassed query since public tracking does not have active auth session
        $tokenRecord = ServiceTrackingToken::withoutGlobalScopes()
            ->where('token_hash', $tokenHash)
            ->first();

        if (! $tokenRecord || ! $tokenRecord->isValid()) {
            return null;
        }

        // Record access audit
        $tokenRecord->update([
            'last_accessed_at' => now(),
            'last_accessed_ip' => $clientIp,
        ]);

        return ServiceOrder::withoutGlobalScopes()
            ->with(['store', 'customer', 'device', 'items'])
            ->find($tokenRecord->service_order_id);
    }

    /**
     * Revoke all active tracking tokens for a given service order.
     */
    public function revokeTokensForOrder(ServiceOrder $order): void
    {
        ServiceTrackingToken::where('service_order_id', $order->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Regenerate tracking token: revokes old ones and creates a fresh token.
     */
    public function regenerateToken(ServiceOrder $order, int $validDays = 90): string
    {
        return DB::transaction(function () use ($order, $validDays) {
            $this->revokeTokensForOrder($order);

            $result = $this->generateToken($order, $validDays);

            // Also keep service_orders.tracking_token updated for legacy/shortcut access
            $order->update([
                'tracking_token' => $result['plain_token'],
            ]);

            return $result['plain_token'];
        });
    }
}
