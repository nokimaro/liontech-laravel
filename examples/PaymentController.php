<?php

// Advanced example: direct card payments (server-side card encryption).
// copy to app/Http/Controllers/PaymentController.php
//
// Use this only if you need your own payment form with card fields.
// For most integrations, use OrderController — create an order and
// redirect the customer to the LionTech hosted payment page instead.
//
// Card encryption flow:
//   Card data (PAN, CVV, expiry) is encrypted server-side by CardEncryptor
//   before being sent to the LionTech API. CardEncryptor is automatically
//   injected by Laravel's service container — configure the public key in .env:
//   LIONTECH_CARD_ENCRYPTION_PUBLIC_KEY=/path/to/card-public.pem
//
// Routes (routes/api.php):
//   Route::post('/payments', [PaymentController::class, 'create']);
//   Route::get('/payments/{paymentId}', [PaymentController::class, 'show']);
//   Route::post('/payments/{paymentId}/confirm', [PaymentController::class, 'confirm']);

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nokimaro\LionTech\Exceptions\SdkException;
use Nokimaro\LionTech\Exceptions\Validation\ValidationException;
use Nokimaro\LionTech\Laravel\Facades\LionTech;
use Nokimaro\LionTech\Requests\CreatePaymentRequest;
use Nokimaro\LionTech\Requests\CustomerData;
use Nokimaro\LionTech\Security\CardEncryptor;
use Nokimaro\LionTech\ValueObjects\Currency;
use Nokimaro\LionTech\ValueObjects\EncryptedCardData;
use Nokimaro\LionTech\ValueObjects\Money;
use Nokimaro\LionTech\ValueObjects\PaymentData;

class PaymentController extends Controller
{
    public function __construct(
        private readonly CardEncryptor $encryptor,
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'order_id' => 'required|string',
            'description' => 'nullable|string',
            'customer_email' => 'required|email',
            'back_link' => 'required|url',
            'webhook_url' => 'nullable|url',
            'card.pan' => 'required|string',
            'card.cvv' => 'required|string',
            'card.exp_month' => 'required|integer|min:1|max:12',
            'card.exp_year' => 'required|integer',
            'card.holder' => 'required|string',
        ]);

        try {
            $encrypted = $this->encryptor->encryptForPayment([
                'pan' => $validated['card']['pan'],
                'cvv' => $validated['card']['cvv'],
                'exp_month' => $validated['card']['exp_month'],
                'exp_year' => $validated['card']['exp_year'],
                'cardHolder' => $validated['card']['holder'],
            ]);

            $payment = LionTech::payments()->create(new CreatePaymentRequest(
                amount: new Money((string) $validated['amount'], Currency::USD),
                paymentData: PaymentData::card(new EncryptedCardData(
                    encryptedCardData: $encrypted['encryptedCardData'],
                    cardHolder: $validated['card']['holder'],
                )),
                customer: new CustomerData(email: $validated['customer_email']),
                orderId: $validated['order_id'],
                backLink: $validated['back_link'],
                webhookUrl: $validated['webhook_url'] ?? null,
                description: $validated['description'] ?? null,
            ));

            return response()->json([
                'success' => true,
                'payment' => $payment,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getErrors(),
            ], 422);

        } catch (SdkException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $paymentId): JsonResponse
    {
        $payment = LionTech::payments()->get($paymentId);

        return response()->json([
            'payment' => $payment,
        ]);
    }

    public function confirm(string $paymentId): JsonResponse
    {
        $payment = LionTech::payments()->confirm($paymentId);

        return response()->json([
            'payment' => $payment,
        ]);
    }
}
