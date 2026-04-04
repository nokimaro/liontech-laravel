<?php

declare(strict_types=1);

namespace Nokimaro\LionTech\Laravel\Tests;

use Nokimaro\LionTech\Client;
use Nokimaro\LionTech\Laravel\LionTechServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [LionTechServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('liontech', [
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'base_url' => 'https://api.sandbox.liontechnology.ai',
            'secure_url' => 'https://secure.sandbox.liontechnology.ai',
            'webhook_public_key' => null,
            'card_encryption_public_key' => null,
        ]);
    }

    /**
     * Create a mock Client instance.
     *
     * @param  array<string, mixed>  $config
     */
    protected function createMockSdk(array $config = []): Client
    {
        $defaultConfig = [
            'access_token' => 'test_access_token',
            'base_url' => 'https://api.sandbox.liontechnology.ai',
            'secure_url' => 'https://secure.sandbox.liontechnology.ai',
        ];

        $merged = array_merge($defaultConfig, $config);

        return new Client(
            accessToken: (string) ($merged['access_token'] ?? ''),
            refreshToken: $merged['refresh_token'] ?? null,
            baseUrl: $merged['base_url'] ?? null,
            secureUrl: $merged['secure_url'] ?? null,
        );
    }
}
