<?php

declare(strict_types=1);

namespace Nokimaro\LionTech\Laravel;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Nokimaro\LionTech\Client;
use Nokimaro\LionTech\Clients\AuthClient;
use Nokimaro\LionTech\Clients\BalancesClient;
use Nokimaro\LionTech\Clients\EncryptionKeyClient;
use Nokimaro\LionTech\Clients\OrdersClient;
use Nokimaro\LionTech\Clients\PaymentsClient;
use Nokimaro\LionTech\Clients\PayoutsClient;
use Nokimaro\LionTech\Clients\RefundsClient;
use Nokimaro\LionTech\Clients\SignatureClient;
use Nokimaro\LionTech\Clients\TokensClient;
use Nokimaro\LionTech\Clients\TransfersClient;
use Nokimaro\LionTech\Laravel\Config\LionTechConfig;
use Nokimaro\LionTech\Security\CardEncryptor;
use Nokimaro\LionTech\Security\WebhookSignatureVerifier;

/**
 * Laravel Service Provider for LionTech Payment Gateway SDK.
 *
 * Registers the SDK and all its clients as singletons in the service container,
 * enabling dependency injection throughout your Laravel application.
 */
final class LionTechServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/liontech.php', 'liontech');

        $this->registerSdk();
        $this->registerClients();
        $this->registerHelpers();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/liontech.php' => config_path('liontech.php'),
        ], 'liontech-config');
    }

    /**
     * Register the main SDK instance.
     */
    protected function registerSdk(): void
    {
        $this->app->singleton(Client::class, function (Container $app): Client {
            /** @var \Illuminate\Contracts\Config\Repository $config */
            $config = $app->make('config');
            /** @var array{access_token?: string|null, refresh_token?: string|null, base_url?: string|null, secure_url?: string|null} $liontechConfig */
            $liontechConfig = $config->get('liontech') ?? [];

            return new Client(
                accessToken: $liontechConfig['access_token'] ?? '',
                refreshToken: $liontechConfig['refresh_token'] ?? null,
                baseUrl: $liontechConfig['base_url'] ?? null,
                secureUrl: $liontechConfig['secure_url'] ?? null,
            );
        });

        $this->app->alias(Client::class, 'liontech');
    }

    /**
     * Register all API clients as singletons.
     */
    protected function registerClients(): void
    {
        $this->app->singleton(AuthClient::class, fn (Container $app) => $app->make(Client::class)->auth());
        $this->app->singleton(OrdersClient::class, fn (Container $app) => $app->make(Client::class)->orders());
        $this->app->singleton(PaymentsClient::class, fn (Container $app) => $app->make(Client::class)->payments());
        $this->app->singleton(RefundsClient::class, fn (Container $app) => $app->make(Client::class)->refunds());
        $this->app->singleton(PayoutsClient::class, fn (Container $app) => $app->make(Client::class)->payouts());
        $this->app->singleton(TokensClient::class, fn (Container $app) => $app->make(Client::class)->tokens());
        $this->app->singleton(BalancesClient::class, fn (Container $app) => $app->make(Client::class)->balances());
        $this->app->singleton(TransfersClient::class, fn (Container $app) => $app->make(Client::class)->transfers());
        $this->app->singleton(SignatureClient::class, fn (Container $app) => $app->make(Client::class)->signature());
        $this->app->singleton(
            EncryptionKeyClient::class,
            fn (Container $app) => $app->make(Client::class)->encryptionKey()
        );
    }

    /**
     * Register helper classes as singletons.
     */
    protected function registerHelpers(): void
    {
        $this->app->singleton(WebhookSignatureVerifier::class, function (Container $app): WebhookSignatureVerifier {
            $publicKey = LionTechConfig::getWebhookPublicKey()
                ?? $app->make(Client::class)->signature()->getPublicKey();

            /** @var string $publicKey */
            return new WebhookSignatureVerifier($publicKey);
        });

        $this->app->singleton(CardEncryptor::class, function (Container $app): CardEncryptor {
            $publicKey = LionTechConfig::getCardEncryptionPublicKey()
                ?? $app->make(Client::class)->encryptionKey()->getPublicKey();

            /** @var string $publicKey */
            return new CardEncryptor($publicKey);
        });
    }
}
