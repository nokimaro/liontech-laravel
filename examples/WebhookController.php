<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nokimaro\LionTech\Laravel\Config\LionTechConfig;
use Nokimaro\LionTech\Laravel\Facades\LionTech;

class WebhookController extends Controller
{
    /**
     * Handle incoming LionTech webhooks
     */
    public function handle(Request $request)
    {
        // Verify webhook signature
        $verifier = LionTech::webhookVerifier();

        $isValid = $verifier->verify($request->headers->all(), $request->getContent());

        if (! $isValid) {
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

        // Handle different event types
        return match ($eventType) {
            'payment.succeeded' => $this->handlePaymentSucceeded($payload),
            'payment.failed' => $this->handlePaymentFailed($payload),
            'payment.authorized' => $this->handlePaymentAuthorized($payload),
            'refund.succeeded' => $this->handleRefundSucceeded($payload),
            'order.completed' => $this->handleOrderCompleted($payload),
            default => $this->handleUnknownEvent($payload),
        };
    }

    /**
     * Handle successful payment
     */
    protected function handlePaymentSucceeded(array $payload)
    {
        $paymentId = $payload['data']['paymentId'] ?? null;
        $orderId = $payload['data']['orderId'] ?? null;

        Log::info('Payment succeeded', [
            'payment_id' => $paymentId,
            'order_id' => $orderId,
        ]);

        // TODO: Update your order status, send emails, etc.
        // Event::dispatch(new PaymentSucceeded($paymentId, $orderId));

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed(array $payload)
    {
        $paymentId = $payload['data']['paymentId'] ?? null;
        $reason = $payload['data']['reason'] ?? 'Unknown';

        Log::warning('Payment failed', [
            'payment_id' => $paymentId,
            'reason' => $reason,
        ]);

        // TODO: Notify customer, update order status, etc.

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Handle authorized payment (requires confirmation)
     */
    protected function handlePaymentAuthorized(array $payload)
    {
        $paymentId = $payload['data']['paymentId'] ?? null;

        Log::info('Payment authorized, needs confirmation', [
            'payment_id' => $paymentId,
        ]);

        // TODO: Mark order as awaiting confirmation
        // You may want to auto-confirm or require manual confirmation

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Handle successful refund
     */
    protected function handleRefundSucceeded(array $payload)
    {
        $refundId = $payload['data']['refundId'] ?? null;
        $paymentId = $payload['data']['paymentId'] ?? null;

        Log::info('Refund succeeded', [
            'refund_id' => $refundId,
            'payment_id' => $paymentId,
        ]);

        // TODO: Update refund status, notify customer, etc.

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Handle completed order
     */
    protected function handleOrderCompleted(array $payload)
    {
        $orderId = $payload['data']['orderId'] ?? null;

        Log::info('Order completed', [
            'order_id' => $orderId,
        ]);

        // TODO: Fulfill order, send confirmation email, etc.

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Handle unknown events
     */
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
