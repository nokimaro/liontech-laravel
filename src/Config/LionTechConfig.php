<?php

declare(strict_types=1);

namespace Nokimaro\LionTech\Laravel\Config;

/**
 * Helper class to work with LionTech configuration.
 *
 * Provides static methods to access and validate the SDK configuration
 * from Laravel's config repository.
 */
final readonly class LionTechConfig
{
    /**
     * Get the SDK configuration array for passing to LionTechSDK.
     *
     * @return array{
     *     accessToken: string,
     *     refreshToken: string|null,
     *     baseUrl: string,
     *     secureUrl: string,
     * }
     */
    public static function toSdkConfig(): array
    {
        /** @var array{access_token?: string|null, refresh_token?: string|null, base_url?: string|null, secure_url?: string|null} $config */
        $config = config('liontech') ?? [];

        return [
            'accessToken' => $config['access_token'] ?? throw new \RuntimeException(
                'LionTech access_token is not configured'
            ),
            'refreshToken' => $config['refresh_token'] ?? null,
            'baseUrl' => $config['base_url'] ?? 'https://api.liontechnology.ai',
            'secureUrl' => $config['secure_url'] ?? 'https://secure.liontechnology.ai',
        ];
    }

    /**
     * Get the webhook public key path or content.
     */
    public static function getWebhookPublicKey(): ?string
    {
        /** @var string|null $key */
        $key = config('liontech.webhook_public_key');

        if ($key === null) {
            return null;
        }

        return self::readKeyFile($key);
    }

    /**
     * Get the card encryption public key path or content.
     */
    public static function getCardEncryptionPublicKey(): ?string
    {
        /** @var string|null $key */
        $key = config('liontech.card_encryption_public_key');

        if ($key === null) {
            return null;
        }

        return self::readKeyFile($key);
    }

    /**
     * Check if the SDK is configured with access token.
     */
    public static function isConfigured(): bool
    {
        $token = config('liontech.access_token');

        return is_string($token) && $token !== '';
    }

    /**
     * Check if sandbox mode is enabled.
     */
    public static function isSandbox(): bool
    {
        $baseUrl = config('liontech.base_url', '');

        return is_string($baseUrl) && str_contains($baseUrl, 'sandbox');
    }

    /**
     * Read a key file if it's a file path, otherwise return the content as-is.
     */
    private static function readKeyFile(string $key): string
    {
        if (is_file($key)) {
            return (string) file_get_contents($key);
        }

        return $key;
    }
}
