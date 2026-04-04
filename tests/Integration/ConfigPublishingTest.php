<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Nokimaro\LionTech\Laravel\LionTechServiceProvider;

it('has publishable config file', function (): void {
    $configFile = __DIR__ . '/../../config/liontech.php';

    expect(file_exists($configFile))
        ->toBeTrue();
});

it('publishes config file to config directory', function (): void {
    $this->artisan('vendor:publish', [
        '--tag' => 'liontech-config',
    ]);

    expect(config_path('liontech.php'))
        ->toBeFile();

    // Clean up
    File::delete(config_path('liontech.php'));
});

it('config file has correct structure', function (): void {
    $config = include __DIR__ . '/../../config/liontech.php';

    expect($config)
        ->toHaveKey('access_token');
    expect($config)
        ->toHaveKey('refresh_token');
    expect($config)
        ->toHaveKey('base_url');
    expect($config)
        ->toHaveKey('secure_url');
    expect($config)
        ->toHaveKey('webhook_public_key');
    expect($config)
        ->toHaveKey('card_encryption_public_key');
});

it('config file has env defaults', function (): void {
    $config = include __DIR__ . '/../../config/liontech.php';

    expect($config['base_url'])->toBe(env('LIONTECH_BASE_URL', 'https://api.liontechnology.ai'));
    expect($config['secure_url'])->toBe(env('LIONTECH_SECURE_URL', 'https://secure.liontechnology.ai'));
});
