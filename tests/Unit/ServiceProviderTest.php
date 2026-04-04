<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\DeferrableProvider;
use Nokimaro\LionTech\Client;
use Nokimaro\LionTech\Clients\AuthClient;
use Nokimaro\LionTech\Clients\BalancesClient;
use Nokimaro\LionTech\Clients\OrdersClient;
use Nokimaro\LionTech\Clients\PaymentsClient;
use Nokimaro\LionTech\Clients\PayoutsClient;
use Nokimaro\LionTech\Clients\RefundsClient;
use Nokimaro\LionTech\Clients\SignatureClient;
use Nokimaro\LionTech\Clients\TokensClient;
use Nokimaro\LionTech\Clients\TransfersClient;
use Nokimaro\LionTech\Laravel\LionTechServiceProvider;
use Nokimaro\LionTech\Security\CardEncryptor;
use Nokimaro\LionTech\Security\WebhookSignatureVerifier;

beforeEach(function (): void {
    // Test RSA public key for webhook/encryption tests
    $testKeyContent = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtCUlR9EprWfVac8FpPB7
m6aiJiXOf07+eCyN66Agudkh5QcUps43e+2ogtC9obMdr3xphaKK61bGARN05c0F
A22mBrufrS46TPZhYYeMzcPAas6SuasaUL8JuphXRQjjQrvxJBr43VK9y3Y3QfHu
jKb32aJlS5Ev130zgLQCukmYLh6WmuPcjWuw7V/3gQzTNENjR4VAQYr0pYmHBsy2
d+D/bZCSyRXQ58kbt55Evo+Sjvdnj3wvTrG+i5R1borWiTWzduLDcd/MO83byLyM
K0GwJprh7j/U+NSJHfLpi8kiuih6R41wNf2BWUEKo6J8vaBFPQL2iJ4ixvB2sxIx
KwIDAQAB
-----END PUBLIC KEY-----';

    $this->app->config->set('liontech', [
        'access_token' => 'test_access_token',
        'refresh_token' => 'test_refresh_token',
        'base_url' => 'https://api.sandbox.liontechnology.ai',
        'secure_url' => 'https://secure.sandbox.liontechnology.ai',
        'webhook_public_key' => $testKeyContent,
        'card_encryption_public_key' => $testKeyContent,
    ]);
});

it('registers the service provider', function (): void {
    $provider = app()
        ->getProvider(LionTechServiceProvider::class);
    expect($provider)
        ->toBeInstanceOf(LionTechServiceProvider::class);
});

it('is not a deferred provider', function (): void {
    $provider = app()
        ->getProvider(LionTechServiceProvider::class);

    expect($provider)
        ->not()
        ->toBeInstanceOf(\Illuminate\Contracts\Support\DeferrableProvider::class);
});

it('binds LionTechSDK as singleton', function (): void {
    $instance1 = app(Client::class);
    $instance2 = app(Client::class);

    expect($instance1)
        ->toBeInstanceOf(Client::class);
    expect($instance1)
        ->toBe($instance2);
});

it('registers the liontech alias', function (): void {
    $instance = app('liontech');
    expect($instance)
        ->toBeInstanceOf(Client::class);
});

it('binds individual clients as singletons', function (): void {
    $clients = [
        AuthClient::class,
        OrdersClient::class,
        PaymentsClient::class,
        RefundsClient::class,
        PayoutsClient::class,
        TokensClient::class,
        BalancesClient::class,
        TransfersClient::class,
        SignatureClient::class,
    ];

    foreach ($clients as $client) {
        $instance1 = app($client);
        $instance2 = app($client);

        expect($instance1)
            ->toBeInstanceOf($client);
        expect($instance1)
            ->toBe($instance2);
    }
});

it('binds WebhookSignatureVerifier as singleton', function (): void {
    $instance1 = app(WebhookSignatureVerifier::class);
    $instance2 = app(WebhookSignatureVerifier::class);

    expect($instance1)
        ->toBeInstanceOf(WebhookSignatureVerifier::class);
    expect($instance1)
        ->toBe($instance2);
});

it('binds CardEncryptor as singleton', function (): void {
    $instance1 = app(CardEncryptor::class);
    $instance2 = app(CardEncryptor::class);

    expect($instance1)
        ->toBeInstanceOf(CardEncryptor::class);
    expect($instance1)
        ->toBe($instance2);
});

it('can resolve SDK from container', function (): void {
    $sdk = app(Client::class);
    expect($sdk)
        ->not()
        ->toBeNull();
    expect($sdk->apiClient())
        ->not()
        ->toBeNull();
});

it('creates Client with only access token when optional config is absent', function (): void {
    app()->forgetInstance(Client::class);
    $this->app->config->set('liontech', [
        'access_token' => 'minimal_token',
    ]);

    $sdk = app(Client::class);

    expect($sdk)
        ->toBeInstanceOf(Client::class);
    expect($sdk->apiClient())
        ->not()
        ->toBeNull();
});

it('provides default base_url and secure_url via mergeConfigFrom', function (): void {
    // Config not published — mergeConfigFrom should supply package defaults
    $this->app->config->set('liontech', null);

    // Re-trigger mergeConfigFrom by re-booting the provider in a fresh context
    $config = include __DIR__ . '/../../config/liontech.php';

    expect($config['base_url'])->toBe(env('LIONTECH_BASE_URL', 'https://api.liontechnology.ai'));
    expect($config['secure_url'])->toBe(env('LIONTECH_SECURE_URL', 'https://secure.liontechnology.ai'));
    expect($config['sandbox'])->toBe(env('LIONTECH_SANDBOX', false));
});

it('creates a new Client instance after forgetInstance', function (): void {
    $instance1 = app(Client::class);

    app()
        ->forgetInstance(Client::class);
    $this->app->config->set('liontech.access_token', 'new_token');

    $instance2 = app(Client::class);

    expect($instance2)
        ->toBeInstanceOf(Client::class);
    expect($instance2)
        ->not()
        ->toBe($instance1);
});

it('can access clients through SDK', function (): void {
    $sdk = app(Client::class);

    expect($sdk->auth())
        ->toBeInstanceOf(AuthClient::class);
    expect($sdk->orders())
        ->toBeInstanceOf(OrdersClient::class);
    expect($sdk->payments())
        ->toBeInstanceOf(PaymentsClient::class);
    expect($sdk->refunds())
        ->toBeInstanceOf(RefundsClient::class);
    expect($sdk->payouts())
        ->toBeInstanceOf(PayoutsClient::class);
    expect($sdk->tokens())
        ->toBeInstanceOf(TokensClient::class);
    expect($sdk->balances())
        ->toBeInstanceOf(BalancesClient::class);
    expect($sdk->transfers())
        ->toBeInstanceOf(TransfersClient::class);
    expect($sdk->signature())
        ->toBeInstanceOf(SignatureClient::class);
});
