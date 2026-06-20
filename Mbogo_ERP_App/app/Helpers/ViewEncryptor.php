<?php
// app/Helpers/ViewEncryptor.php

if (! function_exists('encryptHtmlWithPassword')) {
    /**
     * Encrypt plaintext using AES-256-GCM and PBKDF2.
     * Returns base64 fields and a store_id.
     */
    function encryptHtmlWithPassword(string $plaintext, string $password): array
    {
        $salt = random_bytes(16);
        $iterations = 1500;
        $keyLen = 32;
        $iv = random_bytes(12);
        $cipher = 'aes-256-gcm';

        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, $keyLen, true);

        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // unique id for temporary server-side storage
        $storeId = 'enchtml:' . bin2hex(random_bytes(20));

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv'         => base64_encode($iv),
            'tag'        => base64_encode($tag),
            'salt'       => base64_encode($salt),
            'iterations' => $iterations,
            'algo'       => 'aes-256-gcm',
            'store_id'   => $storeId,
        ];
    }
}