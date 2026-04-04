<?php

// Example: copy to app/Http/Controllers/WebhookController.php
//
// Routes (routes/api.php):
//   Route::post('/webhooks/liontech', [WebhookController::class, 'handle']);
//
// Important: exclude this route from CSRF verification in bootstrap/app.php:
//   ->withMiddleware(function (Middleware $middleware) {
//       $middleware->validateCsrfTokens(except: ['webhooks/liontech']);
//   })

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nokimaro\LionTech\Security\WebhookSignatureVerifier;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookSignatureVerifier $verifier,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifier->verify($request->headers->all(), $request->getContent())) {
            Log::warning('Invalid LionTech webhook signature', [
                'ip' => $request->ip(),
            ]);

            abort(403, 'Invalid signature');
        }

        $payload = $request->all();
        $eventType = $payload['type'] ?? 'unknown';

        Log::info('LionTech webhook received', [
            'event_type' => $eventType,
            'event_id' => $payload['id'] ?? null,
        ]);

        return match ($eventType) {
            'payment.succeeded' => $this->handlePaymentSucceeded($payload),
            'payment.failed' => $this->handlePaymentFailed($payload),
            'payment.authorized' => $this->handlePaymentAuthorized($payload),
            'refund.succeeded' => $this->handleRefundSucceeded($payload),
            'order.completed' => $this->handleOrderCompleted($payload),
            default => $this->handleUnknownEvent($eventType),
        };
    }

    protected function handlePaymentSucceeded(array $payload): JsonResponse
    {
        // TODO: Update your order status, send confirmation email, etc.
        // Event::dispatch(new PaymentSucceeded($payload['data']['paymentId'], $payload['data']['orderId']));

        Log::info('Payment succeeded', [
            'payment_id' => $payload['data']['paymentId'] ?? null,
            'order_id' => $payload['data']['orderId'] ?? null,
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handlePaymentFailed(array $payload): JsonResponse
    {
        // TODO: Notify customer, update order status, etc.

        Log::warning('Payment failed', [
            'payment_id' => $payload['data']['paymentId'] ?? null,
            'reason' => $payload['data']['reason'] ?? 'Unknown',
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handlePaymentAuthorized(array $payload): JsonResponse
    {
        // TODO: Mark order as awaiting confirmation, or auto-confirm via payments()->confirm()

        Log::info('Payment authorized, needs confirmation', [
            'payment_id' => $payload['data']['paymentId'] ?? null,
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handleRefundSucceeded(array $payload): JsonResponse
    {
        // TODO: Update refund status, notify customer, etc.

        Log::info('Refund succeeded', [
            'refund_id' => $payload['data']['refundId'] ?? null,
            'payment_id' => $payload['data']['paymentId'] ?? null,
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handleOrderCompleted(array $payload): JsonResponse
    {
        // TODO: Fulfill order, send confirmation email, etc.

        Log::info('Order completed', [
            'order_id' => $payload['data']['orderId'] ?? null,
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handleUnknownEvent(string $eventType): JsonResponse
    {
        Log::notice('Unknown LionTech webhook event type', [
            'type' => $eventType,
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
