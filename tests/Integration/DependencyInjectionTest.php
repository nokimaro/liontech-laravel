<?php

declare(strict_types=1);

use Nokimaro\LionTech\Client;
use Nokimaro\LionTech\Clients\AuthClient;
use Nokimaro\LionTech\Clients\OrdersClient;
use Nokimaro\LionTech\Clients\PaymentsClient;
use Nokimaro\LionTech\Security\CardEncryptor;
use Nokimaro\LionTech\Security\WebhookSignatureVerifier;

beforeEach(function (): void {
    $testKeyContent = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtCUlR9EprWfVac8FpPB7
m6aiJiXOf07+eCyN66Agudkh5QcUps43e+2ogtC9obMdr3xphaKK61bGARN05c0F
A22mBrufrS46TPZhYYeMzcPAas6SuasaUL8JuphXRQjjQrvxJBr43VK9y3Y3QfHu
jKb32aJlS5Ev130zgLQCukmYLh6WmuPcjWuw7V/3gQzTNENjR4VAQYr0pYmHBsy2
d+D/bZCSyRXQ58kbt55Evo+Sjvdnj3wvTrG+i5R1borWiTWzduLDcd/MO83byLyM
K0GwJprh7j/U+NSJHfLpi8kiuih6R41wNf2BWUEKo6J8vaBFPQL2iJ4ixvB2sxIx
KwIDAQAB
-----END PUBLIC KEY-----';

    app()
        ->config->set('liontech', [
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'base_url' => 'https://api.sandbox.liontechnology.ai',
            'secure_url' => 'https://secure.sandbox.liontechnology.ai',
            'webhook_public_key' => $testKeyContent,
            'card_encryption_public_key' => $testKeyContent,
        ]);
});

it('can inject LionTechSDK into controller', function (): void {
    $sdk = app(Client::class);

    expect($sdk)
        ->toBeInstanceOf(Client::class);
});

it('can inject individual clients', function (): void {
    $authClient = app(AuthClient::class);
    $ordersClient = app(OrdersClient::class);
    $paymentsClient = app(PaymentsClient::class);

    expect($authClient)
        ->toBeInstanceOf(AuthClient::class);
    expect($ordersClient)
        ->toBeInstanceOf(OrdersClient::class);
    expect($paymentsClient)
        ->toBeInstanceOf(PaymentsClient::class);
});

it('can inject helpers', function (): void {
    $webhookVerifier = app(WebhookSignatureVerifier::class);
    $cardEncryptor = app(CardEncryptor::class);

    expect($webhookVerifier)
        ->toBeInstanceOf(WebhookSignatureVerifier::class);
    expect($cardEncryptor)
        ->toBeInstanceOf(CardEncryptor::class);
});

it('supports method injection in route closures', function (): void {
    // This tests that the service container can resolve dependencies
    $closure = (fn (Client $sdk): \Nokimaro\LionTech\Client => $sdk);

    $result = app()
        ->call($closure);
    expect($result)
        ->toBeInstanceOf(Client::class);
});

it('can use facade with custom configuration', function (): void {
    config()->set('liontech.access_token', 'custom_token');
    config()
        ->set('liontech.base_url', 'https://custom.api.liontech.ai');

    // Force re-resolution by clearing instance
    app()
        ->forgetInstance(Client::class);

    $sdk = app(Client::class);
    expect($sdk)
        ->toBeInstanceOf(Client::class);
});

it('resolves helpers from config keys when available', function (): void {
    $testKeyContent = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtCUlR9EprWfVac8FpPB7
m6aiJiXOf07+eCyN66Agudkh5QcUps43e+2ogtC9obMdr3xphaKK61bGARN05c0F
A22mBrufrS46TPZhYYeMzcPAas6SuasaUL8JuphXRQjjQrvxJBr43VK9y3Y3QfHu
jKb32aJlS5Ev130zgLQCukmYLh6WmuPcjWuw7V/3gQzTNENjR4VAQYr0pYmHBsy2
d+D/bZCSyRXQ58kbt55Evo+Sjvdnj3wvTrG+i5R1borWiTWzduLDcd/MO83byLyM
K0GwJprh7j/U+NSJHfLpi8kiuih6R41wNf2BWUEKo6J8vaBFPQL2iJ4ixvB2sxIx
KwIDAQAB
-----END PUBLIC KEY-----';

    // Clear any previously resolved instances
    app()
        ->forgetInstance(WebhookSignatureVerifier::class);
    app()
        ->forgetInstance(CardEncryptor::class);

    // Ensure config has keys set
    app()
        ->config->set('liontech.webhook_public_key', $testKeyContent);
    app()
        ->config->set('liontech.card_encryption_public_key', $testKeyContent);

    $verifier = app(WebhookSignatureVerifier::class);
    $encryptor = app(CardEncryptor::class);

    expect($verifier)
        ->toBeInstanceOf(WebhookSignatureVerifier::class);
    expect($encryptor)
        ->toBeInstanceOf(CardEncryptor::class);
});

it('falls back to SDK signature client when config keys are null', function (): void {
    // Clear any previously resolved instances
    app()
        ->forgetInstance(WebhookSignatureVerifier::class);
    app()
        ->forgetInstance(CardEncryptor::class);
    app()
        ->forgetInstance(Client::class);

    // Set config keys to null to trigger fallback: SDK fetches public key from API
    app()
        ->config->set('liontech.webhook_public_key', null);
    app()
        ->config->set('liontech.card_encryption_public_key', null);

    $verifier = app(WebhookSignatureVerifier::class);

    expect($verifier)
        ->toBeInstanceOf(WebhookSignatureVerifier::class);
});

it('falls back to SDK signature client for CardEncryptor when config key is null', function (): void {
    // Clear any previously resolved instances
    app()
        ->forgetInstance(WebhookSignatureVerifier::class);
    app()
        ->forgetInstance(CardEncryptor::class);
    app()
        ->forgetInstance(Client::class);

    // Set config key to null to trigger fallback: SDK fetches public key from API
    app()
        ->config->set('liontech.card_encryption_public_key', null);

    $encryptor = app(CardEncryptor::class);

    expect($encryptor)
        ->toBeInstanceOf(CardEncryptor::class);
});
