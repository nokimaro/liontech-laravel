<?php

declare(strict_types=1);

namespace Nokimaro\LionTech\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Nokimaro\LionTech\Clients\AuthClient;
use Nokimaro\LionTech\Clients\BalancesClient;
use Nokimaro\LionTech\Clients\OrdersClient;
use Nokimaro\LionTech\Clients\PaymentsClient;
use Nokimaro\LionTech\Clients\PayoutsClient;
use Nokimaro\LionTech\Clients\RefundsClient;
use Nokimaro\LionTech\Clients\SignatureClient;
use Nokimaro\LionTech\Clients\TokensClient;
use Nokimaro\LionTech\Clients\TransfersClient;
use Nokimaro\LionTech\Http\ApiClient;

/**
 * Laravel Facade for LionTech Payment Gateway SDK.
 *
 * Provides a static-like interface to the SDK with full IDE autocompletion.
 *
 * @method static AuthClient auth() Get the authentication client
 * @method static OrdersClient orders() Get the orders client
 * @method static PaymentsClient payments() Get the payments client
 * @method static RefundsClient refunds() Get the refunds client
 * @method static PayoutsClient payouts() Get the payouts client
 * @method static TokensClient tokens() Get the tokens client
 * @method static BalancesClient balances() Get the balances client
 * @method static TransfersClient transfers() Get the transfers client
 * @method static SignatureClient signature() Get the signature client
 * @method static ApiClient apiClient() Get the underlying API client
 *
 * @see \Nokimaro\LionTech\Client
 */
final class LionTech extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'liontech';
    }
}
