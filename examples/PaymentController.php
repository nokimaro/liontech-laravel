<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LionTech\SDK\DTOs\Request\CreatePaymentRequest;
use LionTech\SDK\DTOs\Request\CustomerData;
use LionTech\SDK\Exceptions\ApiExceptionMapper;
use LionTech\SDK\Exceptions\Validation\ValidationException;
use LionTech\SDK\ValueObjects\Currency;
use LionTech\SDK\ValueObjects\Money;
use Nokimaro\LionTech\Laravel\Facades\LionTech;

class PaymentController extends Controller
{
    /**
     * Create a new payment
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'order_id' => 'required|string',
            'description' => 'nullable|string',
            'customer_email' => 'required|email',
            'return_url' => 'required|url',
        ]);

        try {
            $paymentRequest = new CreatePaymentRequest(
                amount: new Money(amountInCents: (int) ($validated['amount'] * 100), currency: Currency::USD),
                orderId: $validated['order_id'],
                description: $validated['description'] ?? 'Payment',
                returnUrl: $validated['return_url'],
                customerData: new CustomerData(email: $validated['customer_email']),
            );

            $payment = LionTech::payments()->create($paymentRequest);

            return response()->json([
                'success' => true,
                'payment' => $payment,
                'confirmation_url' => $payment->confirmationUrl ?? null,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getErrors(),
            ], 422);

        } catch (\Exception $e) {
            // Map API exceptions to appropriate exception types
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
