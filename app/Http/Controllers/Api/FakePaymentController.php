<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmFakePaymentRequest;
use App\Http\Requests\CreateFakePaymentSessionRequest;
use App\Models\PaymentSession;
use App\Services\FakePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FakePaymentController extends Controller
{
    public function __construct(
        private readonly FakePaymentService $fakePaymentService
    ) {
    }

    public function createSession(CreateFakePaymentSessionRequest $request): JsonResponse
    {
        $session = $this->fakePaymentService->createSession($request->user(), $request->validated());

        return response()->json($this->toBasePayload($session) + [
            'qr_content' => rtrim((string) config('app.url'), '/') . '/fake-pay/' . $session->id,
        ], 201);
    }

    public function scan(Request $request, string $id): JsonResponse
    {
        $session = $this->findOwnedSessionOrFail($request, $id);
        $scanResult = $this->fakePaymentService->markScanned($session);
        /** @var PaymentSession $updated */
        $updated = $scanResult['session'];

        return response()->json($this->toBasePayload($updated) + [
            'scanned_at' => $updated->scanned_at?->toIso8601String(),
            'confirm_token' => $scanResult['confirm_token'],
        ]);
    }

    public function confirm(ConfirmFakePaymentRequest $request, string $id): JsonResponse
    {
        $session = $this->findOwnedSessionOrFail($request, $id);
        $updated = $this->fakePaymentService->confirm($session, (string) $request->validated('confirm_token'));

        return response()->json($this->toBasePayload($updated) + [
            'paid_at' => $updated->paid_at?->toIso8601String(),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $session = $this->findOwnedSessionOrFail($request, $id);

        return response()->json($this->toBasePayload($session) + [
            'scanned_at' => $session->scanned_at?->toIso8601String(),
            'paid_at' => $session->paid_at?->toIso8601String(),
        ]);
    }

    private function findOwnedSessionOrFail(Request $request, string $id): PaymentSession
    {
        return PaymentSession::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function toBasePayload(PaymentSession $session): array
    {
        return [
            'session_id' => $session->id,
            'status' => $session->status,
            'amount' => (float) $session->amount,
            'currency' => $session->currency,
            'payment_code' => $session->payment_code,
            'expires_at' => $session->expires_at?->toIso8601String(),
        ];
    }
}
