<?php

declare(strict_types=1);

namespace Nokimaro\LionTech\Laravel\Config;

/**
 * Helper class to work with LionTech configuration.
 *
 * Provides static methods to access and validate the SDK configuration.
 */
final readonly class LionTechConfig
{
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
        return (bool) config('liontech.sandbox', false);
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
