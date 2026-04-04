<?php

// Example: copy to app/Http/Controllers/PaymentResultController.php
//
// Handles redirects from LionTech after customer completes or declines payment.
// LionTech redirects to successUrl / declineUrl you set when creating the order.
//
// Routes (routes/web.php):
//   Route::get('/payment/success', [PaymentResultController::class, 'success'])
//       ->name('payment.success');
//   Route::get('/payment/decline', [PaymentResultController::class, 'decline'])
//       ->name('payment.decline');
//
// Note: order_id should be passed via query string or session.
// The safest approach is to store order_id in the session when creating the order
// and read it back here, rather than trusting a query parameter.

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Nokimaro\LionTech\Enums\OrderStatus;
use Nokimaro\LionTech\Laravel\Facades\LionTech;

class PaymentResultController extends Controller
{
    /**
     * Customer returned after successful payment.
     *
     * LionTech redirects here after payment — but the actual payment confirmation
     * should come via webhook (WebhookController). Do not mark the order as paid
     * solely based on this redirect; use it only to show a "thank you" page.
     */
    public function success(Request $request): View|RedirectResponse
    {
        // Read order_id from session (set during order creation)
        $orderId = $request->session()
            ->pull('liontech_order_id');

        if (! $orderId) {
            return redirect()->route('home');
        }

        // Verify actual order status via API — do not trust the redirect alone
        $order = LionTech::orders()->get($orderId);

        if ($order->status === OrderStatus::PAID) {
            return view('payment.success', [
                'order' => $order,
            ]);
        }

        // Payment may still be processing — show a pending page
        return view('payment.pending', [
            'order' => $order,
        ]);
    }

    /**
     * Customer returned after declining or failing payment.
     */
    public function decline(Request $request): View|RedirectResponse
    {
        $orderId = $request->session()
            ->pull('liontech_order_id');

        if (! $orderId) {
            return redirect()->route('home');
        }

        $order = LionTech::orders()->get($orderId);

        return view('payment.decline', [
            'order' => $order,
        ]);
    }
}
