<?php

declare(strict_types=1);

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
use Nokimaro\LionTech\Laravel\Facades\LionTech;
use Nokimaro\LionTech\Security\CardEncryptor;
use Nokimaro\LionTech\Security\WebhookSignatureVerifier;

beforeEach(function (): void {
    $this->app->config->set('liontech', [
        'access_token' => 'test_access_token',
        'refresh_token' => 'test_refresh_token',
        'base_url' => 'https://api.sandbox.liontechnology.ai',
        'secure_url' => 'https://secure.sandbox.liontechnology.ai',
    ]);
});

it('can access SDK through facade', function (): void {
    expect(LionTech::apiClient())->not()->toBeNull();
});

it('can access auth client through facade', function (): void {
    expect(LionTech::auth())->toBeInstanceOf(AuthClient::class);
});

it('can access orders client through facade', function (): void {
    expect(LionTech::orders())->toBeInstanceOf(OrdersClient::class);
});

it('can access payments client through facade', function (): void {
    expect(LionTech::payments())->toBeInstanceOf(PaymentsClient::class);
});

it('can access refunds client through facade', function (): void {
    expect(LionTech::refunds())->toBeInstanceOf(RefundsClient::class);
});

it('can access payouts client through facade', function (): void {
    expect(LionTech::payouts())->toBeInstanceOf(PayoutsClient::class);
});

it('can access tokens client through facade', function (): void {
    expect(LionTech::tokens())->toBeInstanceOf(TokensClient::class);
});

it('can access balances client through facade', function (): void {
    expect(LionTech::balances())->toBeInstanceOf(BalancesClient::class);
});

it('can access transfers client through facade', function (): void {
    expect(LionTech::transfers())->toBeInstanceOf(TransfersClient::class);
});

it('can access signature client through facade', function (): void {
    expect(LionTech::signature())->toBeInstanceOf(SignatureClient::class);
});

it('can access encryption key client through facade', function (): void {
    expect(LionTech::encryptionKey())->toBeInstanceOf(EncryptionKeyClient::class);
});

it('facade root is the same instance as container resolution', function (): void {
    $fromContainer = app(Client::class);
    $fromFacade = LionTech::getFacadeRoot();

    expect($fromFacade)
        ->toBe($fromContainer);
});

it('returns same instance on repeated calls', function (): void {
    $instance1 = LionTech::auth();
    $instance2 = LionTech::auth();

    expect($instance1)
        ->toBe($instance2);
});

it('can swap facade instance', function (): void {
    $original = LionTech::getFacadeRoot();

    // Create a new instance to swap with
    $newSdk = new Client(
        accessToken: 'swapped_token',
        baseUrl: 'https://api.test.liontech.ai',
        secureUrl: 'https://secure.test.liontech.ai',
    );

    LionTech::swap($newSdk);

    $resolved = LionTech::getFacadeRoot();
    expect($resolved)
        ->toBe($newSdk);
    expect($resolved)
        ->not()
        ->toBe($original);

    // Restore original
    LionTech::swap($original);
});
