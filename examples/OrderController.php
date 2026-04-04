<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LionTech\SDK\DTOs\Request\CreateOrderRequest;
use LionTech\SDK\DTOs\Request\CreateRefundRequest;
use LionTech\SDK\DTOs\Request\CustomerData;
use LionTech\SDK\ValueObjects\Currency;
use LionTech\SDK\ValueObjects\Money;
use Nokimaro\LionTech\Laravel\Config\LionTechConfig;
use Nokimaro\LionTech\Laravel\Facades\LionTech;

class OrderController extends Controller
{
    /**
     * Create a new order
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
        ]);

        $orderRequest = new CreateOrderRequest(
            amount: new Money(amountInCents: (int) ($validated['amount'] * 100), currency: Currency::USD),
            description: $validated['description'],
            customerData: new CustomerData(
                email: $validated['customer_email'] ?? null,
                phone: $validated['customer_phone'] ?? null,
            ),
        );

        $order = LionTech::orders()->create($orderRequest);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Get order details
     */
    public function show(string $orderId)
    {
        $order = LionTech::orders()->get($orderId);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Cancel an order
     */
    public function cancel(string $orderId)
    {
        $order = LionTech::orders()->cancel($orderId);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Process a refund for an order
     */
    public function refund(Request $request, string $orderId)
    {
        $validated = $request->validate([
            'payment_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $refundRequest = new CreateRefundRequest(
            paymentId: $validated['payment_id'],
            amount: new Money(amountInCents: (int) ($validated['amount'] * 100), currency: Currency::USD),
            reason: $validated['reason'] ?? 'Refund request',
        );

        $refund = LionTech::refunds()->create($refundRequest);

        return response()->json([
            'success' => true,
            'refund' => $refund,
        ]);
    }

    /**
     * Get merchant account balances
     */
    public function balances()
    {
        $balances = LionTech::balances()->list();

        return response()->json([
            'success' => true,
            'balances' => $balances,
        ]);
    }

    /**
     * List saved payment methods for a customer
     */
    public function savedPaymentMethods(Request $request)
    {
        $accountId = $request->query('account_id');
        $email = $request->query('email');

        $methods = LionTech::tokens()->list(accountId: $accountId, email: $email);

        return response()->json([
            'success' => true,
            'methods' => $methods,
        ]);
    }

    /**
     * Check SDK configuration status
     */
    public function status()
    {
        return response()->json([
            'configured' => LionTechConfig::isConfigured(),
            'sandbox' => LionTechConfig::isSandbox(),
            'base_url' => config('liontech.base_url'),
        ]);
    }
}
