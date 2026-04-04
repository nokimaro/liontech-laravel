---
name: "Bug: CardEncryptor fails with RSA PublicKey - setEncryptionMode() undefined"
about: CardEncryptor constructor crashes when loading an RSA public key
title: "CardEncryptor: PublicKey::setEncryptionMode() method does not exist"
labels: bug
assignees: ""
---

## Describe the bug

`CardEncryptor` crashes during construction when given an RSA public key PEM string. The error occurs because `phpseclib3\Crypt\RSA\PublicKey` does not have a `setEncryptionMode()` method, but `CardEncryptor` tries to call it.

## Error

```
Error: Call to undefined method phpseclib3\Crypt\RSA\PublicKey::setEncryptionMode()

at vendor/liontech/liontech-php-sdk/src/Helpers/CardEncryptor.php:20
     16▕     ) {
     17▕         $key = PublicKeyLoader::load($this->publicKeyPem);
     18▕         assert($key instanceof RSA);
     19▕         $this->rsa = $key;
  ➜  20▕         $this->rsa->setEncryptionMode(RSA::ENCRYPTION_OAEP);
     21▕         $this->rsa->setHash('sha256');
     22▕         $this->rsa->setMGFHash('sha256');
     23▕     }
```

## To Reproduce

```php
use LionTech\SDK\Helpers\CardEncryptor;

$publicKey = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtCUlR9EprWfVac8FpPB7
m6aiJiXOf07+eCyN66Agudkh5QcUps43e+2ogtC9obMdr3xphaKK61bGARN05c0F
A22mBrufrS46TPZhYYeMzcPAas6SuasaUL8JuphXRQjjQrvxJBr43VK9y3Y3QfHu
jKb32aJlS5Ev130zgLQCukmYLh6WmuPcjWuw7V/3gQzTNENjR4VAQYr0pYmHBsy2
d+D/bZCSyRXQ58kbt55Evo+Sjvdnj3wvTrG+i5R1borWiTWzduLDcd/MO83byLyM
K0GwJprh7j/U+NSJHfLpi8kiuih6R41wNf2BWUEKo6J8vaBFPQL2iJ4ixvB2sxIx
KwIDAQAB
-----END PUBLIC KEY-----";

$encryptor = new CardEncryptor($publicKey); // 💥 Crashes here
```

## Expected behavior

`CardEncryptor` should accept a public key and be able to encrypt card data using RSA-OAEP-256.

## Root cause

In phpseclib3, `PublicKey` (returned by `PublicKeyLoader::load()` for public key PEMs) does **not** support encryption — only decryption. The `setEncryptionMode()`, `setHash()`, and `setMGFHash()` methods exist on `PrivateKey` or `RSA` objects, not on `PublicKey`.

## Environment

- **PHP SDK version**: dev-master
- **PHP version**: 8.3, 8.4, 8.5
- **phpseclib version**: ^3.0
- **Laravel SDK version**: 1.0.0 (depends on PHP SDK)

## Impact

This bug makes `CardEncryptor` completely unusable. Any code path that instantiates `CardEncryptor` (including Laravel service provider registration) will crash.

## Suggested fix

Two possible approaches:

### Option A: Use phpseclib3's Crypt\RSA directly
```php
use phpseclib3\Crypt\RSA;

public function __construct(string $publicKeyPem)
{
    $key = RSA::load($publicKeyPem);
    $this->rsa = $key->withPadding(RSA::ENCRYPTION_OAEP)
                     ->withHash('sha256')
                     ->withMGFHash('sha256');
}
```

### Option B: Use the public key for encryption only
If the intent is encryption (not signing), ensure the object supports encryption operations. phpseclib3's public keys *can* encrypt — the API is different from private key operations.

## Additional context

Discovered while writing tests for the Laravel SDK package. Both `WebhookSignatureVerifier` and `CardEncryptor` tests were written — `WebhookSignatureVerifier` works correctly, but `CardEncryptor` fails on construction.
