<?php

declare(strict_types=1);

/**
 * LionTech Payment Gateway Configuration
 *
 * Publish this config with: php artisan vendor:publish --tag=liontech-config
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Access Token
    |--------------------------------------------------------------------------
    |
    | Your LionTech API access token. This is required for authenticated API calls.
    | You can obtain this from your LionTech merchant dashboard.
    |
    */

    'access_token' => env('LIONTECH_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | Set to true to indicate that the package is running in sandbox mode.
    | This is used by LionTechConfig::isSandbox() to check the current mode.
    |
    */

    'sandbox' => env('LIONTECH_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token
    |--------------------------------------------------------------------------
    |
    | Optional refresh token for automatic token rotation.
    | Used when the access token expires and needs to be refreshed.
    |
    */

    'refresh_token' => env('LIONTECH_REFRESH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the LionTech API.
    | Production: https://api.liontechnology.ai
    | Sandbox: https://api.sandbox.liontechnology.ai
    |
    */

    'base_url' => env('LIONTECH_BASE_URL', 'https://api.liontechnology.ai'),

    /*
    |--------------------------------------------------------------------------
    | Secure URL
    |--------------------------------------------------------------------------
    |
    | The secure URL for encryption operations (card encryption, etc.).
    | Production: https://secure.liontechnology.ai
    | Sandbox: https://secure.sandbox.liontechnology.ai
    |
    */

    'secure_url' => env('LIONTECH_SECURE_URL', 'https://secure.liontechnology.ai'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Public Key
    |--------------------------------------------------------------------------
    |
    | Path to the public key file for webhook signature verification.
    | Leave null to use the default key from LionTech.
    |
    | Example: storage_path('keys/liontech-public.pem')
    |
    */

    'webhook_public_key' => env('LIONTECH_WEBHOOK_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Card Encryption Public Key
    |--------------------------------------------------------------------------
    |
    | Path to the public key file for card data encryption.
    | Leave null to use the default key from LionTech.
    |
    | Example: storage_path('keys/liontech-card-public.pem')
    |
    */

    'card_encryption_public_key' => env('LIONTECH_CARD_ENCRYPTION_PUBLIC_KEY'),

];
