<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Nokimaro\LionTech\Laravel\Config\LionTechConfig;
use Nokimaro\LionTech\Laravel\Facades\LionTech;
use Nokimaro\LionTech\Requests\CreateOrderRequest;
use Nokimaro\LionTech\Requests\CreateRefundRequest;
use Nokimaro\LionTech\Requests\CustomerData;
use Nokimaro\LionTech\ValueObjects\Currency;
use Nokimaro\LionTech\ValueObjects\Money;

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
            'success_url' => 'required|url',
            'decline_url' => 'required|url',
            'webhook_url' => 'nullable|url',
        ]);

        $order = LionTech::orders()->create(new CreateOrderRequest(
            amount: new Money((string) $validated['amount'], Currency::USD),
            customer: new CustomerData(
                email: $validated['customer_email'] ?? null,
                phone: $validated['customer_phone'] ?? null,
            ),
            declineUrl: $validated['decline_url'],
            successUrl: $validated['success_url'],
            webhookUrl: $validated['webhook_url'] ?? null,
            description: $validated['description'],
        ));

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
     * Process a refund for a payment
     */
    public function refund(Request $request)
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
        $methods = LionTech::tokens()->list(
            accountId: $request->query('account_id'),
            email: $request->query('email'),
        );

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
