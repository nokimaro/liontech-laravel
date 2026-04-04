<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Nokimaro\LionTech\Exceptions\Validation\ValidationException;
use Nokimaro\LionTech\Http\ApiExceptionMapper;
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

    /**
     * Create a new payment with card data
     */
    public function create(Request $request)
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

        } catch (\Exception $e) {
            $mapped = ApiExceptionMapper::map($e);

            return response()->json([
                'success' => false,
                'error' => $mapped->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment details
     */
    public function show(string $paymentId)
    {
        $payment = LionTech::payments()->get($paymentId);

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }

    /**
     * Confirm an authorized payment
     */
    public function confirm(string $paymentId)
    {
        $payment = LionTech::payments()->confirm($paymentId);

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }
}
