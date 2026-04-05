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
use Nokimaro\LionTech\Webhooks\WebhookPayload;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookSignatureVerifier $verifier,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();

        if (! $this->verifier->verify($request->headers->all(), $rawBody)) {
            Log::warning('Invalid LionTech webhook signature', [
                'ip' => $request->ip(),
            ]);

            abort(403, 'Invalid signature');
        }

        $webhook = WebhookPayload::fromJson($rawBody);

        if ($webhook->error?->hasError()) {
            Log::error('LionTech webhook error', [
                'code' => $webhook->error->code,
                'description' => $webhook->error->description,
                'payment_id' => $webhook->payment->paymentId,
            ]);

            return response()->json([
                'status' => 'ok',
            ]);
        }

        $payment = $webhook->payment;

        Log::info('LionTech webhook received', [
            'event_type' => $webhook->type->value,
            'payment_id' => $payment->paymentId,
            'order_id' => $payment->orderId,
            'status' => $payment->status->value,
        ]);

        if ($payment->isSuccessful()) {
            // TODO: fulfil the order, send confirmation email, etc.
            // Event::dispatch(new PaymentSucceeded($payment->paymentId, $payment->orderId));
        } elseif ($payment->isDeclined()) {
            // TODO: notify customer, mark order as failed, etc.
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
