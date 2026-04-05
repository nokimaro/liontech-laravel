<?php

declare(strict_types=1);

use Nokimaro\LionTech\Laravel\Config\LionTechConfig;

beforeEach(function (): void {
    $this->app->config->set('liontech', [
        'access_token' => 'test_access_token',
        'refresh_token' => 'test_refresh_token',
        'base_url' => 'https://api.sandbox.liontechnology.ai',
        'secure_url' => 'https://secure.sandbox.liontechnology.ai',
        'sandbox' => false,
        'webhook_public_key' => null,
        'card_encryption_public_key' => null,
    ]);
});

it('returns null for webhook public key when not set', function (): void {
    $key = LionTechConfig::getWebhookPublicKey();
    expect($key)
        ->toBeNull();
});

it('returns null for card encryption public key when not set', function (): void {
    $key = LionTechConfig::getCardEncryptionPublicKey();
    expect($key)
        ->toBeNull();
});

it('detects configured SDK when access token is present', function (): void {
    expect(LionTechConfig::isConfigured())->toBeTrue();
});

it('detects unconfigured SDK when access token is missing', function (): void {
    $this->app->config->set('liontech.access_token', null);
    expect(LionTechConfig::isConfigured())->toBeFalse();
});

it('detects unconfigured SDK when access token is empty string', function (): void {
    $this->app->config->set('liontech.access_token', '');
    expect(LionTechConfig::isConfigured())->toBeFalse();
});

it('detects sandbox mode when sandbox config is true', function (): void {
    $this->app->config->set('liontech.sandbox', true);
    expect(LionTechConfig::isSandbox())->toBeTrue();
});

it('detects production mode when sandbox config is false', function (): void {
    $this->app->config->set('liontech.sandbox', false);
    expect(LionTechConfig::isSandbox())->toBeFalse();
});

it('detects production mode when sandbox key is absent from config', function (): void {
    $this->app->config->set('liontech', [
        'access_token' => 'token',
    ]);
    expect(LionTechConfig::isSandbox())->toBeFalse();
});

it('reads webhook public key from file when path is provided', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_key');
    file_put_contents($tempFile, 'test_public_key_content');

    $this->app->config->set('liontech.webhook_public_key', $tempFile);

    $key = LionTechConfig::getWebhookPublicKey();
    expect($key)
        ->toBe('test_public_key_content');

    unlink($tempFile);
});

it('reads card encryption public key from file when path is provided', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_key');
    file_put_contents($tempFile, 'card_public_key_content');

    $this->app->config->set('liontech.card_encryption_public_key', $tempFile);

    $key = LionTechConfig::getCardEncryptionPublicKey();
    expect($key)
        ->toBe('card_public_key_content');

    unlink($tempFile);
});

it('returns PEM content as-is when key is not a file path', function (): void {
    $pemContent = '-----BEGIN PUBLIC KEY-----MIIBIjAN-----END PUBLIC KEY-----';
    $this->app->config->set('liontech.webhook_public_key', $pemContent);

    $key = LionTechConfig::getWebhookPublicKey();
    expect($key)
        ->toBe($pemContent);
});

it('returns null when key config value is empty string', function (): void {
    $this->app->config->set('liontech.webhook_public_key', '');
    expect(LionTechConfig::getWebhookPublicKey())->toBeNull();
});

it('throws ErrorException when key file cannot be read', function (): void {
    $unreadableFile = tempnam(sys_get_temp_dir(), 'test_key');
    file_put_contents($unreadableFile, 'content');
    chmod($unreadableFile, 0000);

    try {
        $this->app->config->set('liontech.webhook_public_key', $unreadableFile);
        LionTechConfig::getWebhookPublicKey();
    } finally {
        chmod($unreadableFile, 0644);
        unlink($unreadableFile);
    }
})->throws(\ErrorException::class);
