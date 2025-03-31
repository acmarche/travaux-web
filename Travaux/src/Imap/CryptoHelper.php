<?php

namespace AcMarche\Travaux\Imap;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CryptoHelper
{
    private const METHOD = 'aes-256-cbc';

    public function __construct(
        #[Autowire(env: 'APP_SECRET')]
        private readonly string $key
    ) {
    }

    public function encrypt(string $data): string
    {
        $iv = random_bytes(openssl_cipher_iv_length(self::METHOD));
        $encrypted = openssl_encrypt($data, self::METHOD, $this->key, 0, $iv);

        return base64_encode($iv.$encrypted);
    }

    public function decrypt(string $data): string
    {
        $decoded = base64_decode($data);
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        return openssl_decrypt($encrypted, self::METHOD, $this->key, 0, $iv);
    }
}