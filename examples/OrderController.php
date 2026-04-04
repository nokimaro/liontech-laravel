<?php

// Example: copy to app/Http/Controllers/OrderController.php
//
// Typical integration flow:
//   1. create() — create an order, redirect customer to payUrl
//   2. Customer pays on LionTech's hosted page
//   3. LionTech redirects to successUrl / declineUrl
//   4. LionTech sends a webhook — handle it in WebhookController
//
// Routes (routes/api.php or routes/web.php):
//   Route::post('/orders', [OrderController::class, 'create']);
//   Route::get('/orders/{orderId}', [OrderController::class, 'show']);
//   Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancel']);
//   Route::post('/refunds', [OrderController::class, 'refund']);

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Nokimaro\LionTech\Laravel\Facades\LionTech;
use Nokimaro\LionTech\Requests\CreateOrderRequest;
use Nokimaro\LionTech\Requests\CreateRefundRequest;
use Nokimaro\LionTech\Requests\CustomerData;
use Nokimaro\LionTech\ValueObjects\Currency;
use Nokimaro\LionTech\ValueObjects\Money;

class OrderController extends Controller
{
    /**
     * Create an order and redirect the customer to the LionTech payment page.
     */
    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'customer_email' => 'nullable|email',
        ]);

        $order = LionTech::orders()->create(new CreateOrderRequest(
            amount: new Money((string) $validated['amount'], Currency::USD),
            customer: new CustomerData(email: $validated['customer_email'] ?? null),
            declineUrl: route('payment.decline'),
            successUrl: route('payment.success'),
            webhookUrl: route('webhooks.liontech'),
            description: $validated['description'],
        ));

        // Store order_id in session so PaymentResultController can verify the result
        $request->session()
            ->put('liontech_order_id', $order->orderId);

        // Redirect customer to LionTech hosted payment page
        return redirect($order->payUrl);
    }

    /**
     * Get order status (e.g. for polling or admin panel).
     */
    public function show(string $orderId): JsonResponse
    {
        $order = LionTech::orders()->get($orderId);

        return response()->json([
            'order_id' => $order->orderId,
            'status' => $order->status,
            'amount' => $order->amount,
            'paid_amount' => $order->paidAmount,
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancel(string $orderId): JsonResponse
    {
        $order = LionTech::orders()->cancel($orderId);

        return response()->json([
            'order_id' => $order->orderId,
            'status' => $order->status,
        ]);
    }

    /**
     * Refund a payment.
     */
    public function refund(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $refund = LionTech::refunds()->create(new CreateRefundRequest(
            amount: new Money((string) $validated['amount'], Currency::USD),
            paymentId: $validated['payment_id'],
        ));

        return response()->json([
            'refund' => $refund,
        ]);
    }
}
