<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSession;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FakePaymentService
{
    public function createSession(User $user, array $payload): PaymentSession
    {
        $orderId = $payload['order_id'] ?? null;
        if ($orderId !== null) {
            $ownsOrder = Order::query()
                ->where('id', (int) $orderId)
                ->where('user_id', $user->id)
                ->exists();

            if (! $ownsOrder) {
                throw new AccessDeniedHttpException('Đơn hàng không thuộc tài khoản hiện tại.');
            }
        }

        $session = PaymentSession::create([
            'user_id' => $user->id,
            'order_id' => $orderId ? (int) $orderId : null,
            'payment_code' => $this->generatePaymentCode(),
            'amount' => round((float) $payload['amount'], 2),
            'currency' => strtoupper((string) ($payload['currency'] ?? 'VND')),
            'status' => PaymentSession::STATUS_PENDING,
            'expires_at' => now()->addMinutes(15),
        ]);

        return $session->fresh();
    }

    public function markScanned(PaymentSession $session): array
    {
        if ($session->status === PaymentSession::STATUS_PAID) {
            throw new ConflictHttpException('Phiên thanh toán đã hoàn tất.');
        }

        if ($this->isExpired($session)) {
            $this->markExpired($session);
            throw new UnprocessableEntityHttpException('Phiên thanh toán đã hết hạn.');
        }

        if ($session->status === PaymentSession::STATUS_PENDING) {
            $session->forceFill([
                'status' => PaymentSession::STATUS_SCANNED,
                'scanned_at' => now(),
            ])->save();
        }

        $plainToken = Str::random(40);
        $metadata = $session->metadata ?? [];
        $metadata['confirm_token_hash'] = hash('sha256', $plainToken);
        $metadata['confirm_token_expires_at'] = now()->addMinutes(5)->toIso8601String();
        $session->metadata = $metadata;
        $session->save();

        return [
            'confirm_token' => $plainToken,
            'session' => $session->fresh(),
        ];
    }

    public function confirm(PaymentSession $session, string $confirmToken): PaymentSession
    {
        if ($session->status === PaymentSession::STATUS_PAID) {
            return $session;
        }

        if ($this->isExpired($session)) {
            $this->markExpired($session);
            throw new UnprocessableEntityHttpException('Phiên thanh toán đã hết hạn.');
        }

        if ($session->status === PaymentSession::STATUS_EXPIRED || $session->status === PaymentSession::STATUS_CANCELLED) {
            throw new UnprocessableEntityHttpException('Phiên thanh toán không còn hợp lệ để xác nhận.');
        }

        $metadata = $session->metadata ?? [];
        $tokenHash = $metadata['confirm_token_hash'] ?? null;
        $tokenExpiresAtRaw = $metadata['confirm_token_expires_at'] ?? null;
        $tokenExpiresAt = $tokenExpiresAtRaw ? Carbon::parse($tokenExpiresAtRaw) : null;
        if (
            ! $tokenHash
            || ! hash_equals($tokenHash, hash('sha256', $confirmToken))
            || ! $tokenExpiresAt
            || $tokenExpiresAt->isPast()
        ) {
            throw new AccessDeniedHttpException('confirm_token không hợp lệ hoặc đã hết hạn.');
        }

        DB::transaction(function () use ($session, $metadata): void {
            unset($metadata['confirm_token_hash'], $metadata['confirm_token_expires_at']);
            $session->forceFill([
                'status' => PaymentSession::STATUS_PAID,
                'paid_at' => now(),
                'metadata' => $metadata,
            ])->save();
        });

        return $session->fresh();
    }

    public function expireOverdueSessions(): int
    {
        return PaymentSession::query()
            ->whereIn('status', [PaymentSession::STATUS_PENDING, PaymentSession::STATUS_SCANNED])
            ->where('expires_at', '<=', now())
            ->update([
                'status' => PaymentSession::STATUS_EXPIRED,
                'updated_at' => now(),
            ]);
    }

    private function isExpired(PaymentSession $session): bool
    {
        return $session->expires_at->isPast();
    }

    private function markExpired(PaymentSession $session): void
    {
        if ($session->status !== PaymentSession::STATUS_EXPIRED && $session->status !== PaymentSession::STATUS_PAID) {
            $session->forceFill(['status' => PaymentSession::STATUS_EXPIRED])->save();
        }
    }

    private function generatePaymentCode(): string
    {
        do {
            $code = 'PAY-' . strtoupper(Str::random(6));
            $exists = PaymentSession::query()->where('payment_code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
