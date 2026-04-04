<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nokimaro\LionTech\Security\WebhookSignatureVerifier;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookSignatureVerifier $verifier,
    ) {
    }

    /**
     * Handle incoming LionTech webhooks
     */
    public function handle(Request $request)
    {
        if (! $this->verifier->verify($request->headers->all(), $request->getContent())) {
            Log::warning('Invalid webhook signature received', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
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
            default => $this->handleUnknownEvent($payload),
        };
    }

    protected function handlePaymentSucceeded(array $payload)
    {
        Log::info('Payment succeeded', [
            'payment_id' => $payload['data']['paymentId'] ?? null,
            'order_id' => $payload['data']['orderId'] ?? null,
        ]);

        // TODO: Update your order status, send emails, etc.
        // Event::dispatch(new PaymentSucceeded($paymentId, $orderId));

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handlePaymentFailed(array $payload)
    {
        Log::warning('Payment failed', [
            'payment_id' => $payload['data']['paymentId'] ?? null,
            'reason' => $payload['data']['reason'] ?? 'Unknown',
        ]);

        // TODO: Notify customer, update order status, etc.

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handlePaymentAuthorized(array $payload)
    {
        Log::info('Payment authorized, needs confirmation', [
            'payment_id' => $payload['data']['paymentId'] ?? null,
        ]);

        // TODO: Mark order as awaiting confirmation

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handleRefundSucceeded(array $payload)
    {
        Log::info('Refund succeeded', [
            'refund_id' => $payload['data']['refundId'] ?? null,
            'payment_id' => $payload['data']['paymentId'] ?? null,
        ]);

        // TODO: Update refund status, notify customer, etc.

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handleOrderCompleted(array $payload)
    {
        Log::info('Order completed', [
            'order_id' => $payload['data']['orderId'] ?? null,
        ]);

        // TODO: Fulfill order, send confirmation email, etc.

        return response()->json([
            'status' => 'ok',
        ]);
    }

    protected function handleUnknownEvent(array $payload)
    {
        Log::notice('Unknown webhook event', [
            'payload' => $payload,
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
