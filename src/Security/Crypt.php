<?php

namespace AbmmHasan\Toolbox\Security;

use DateTime;
use Exception;

final class Crypt
{
    /**
     * Cipher algorithm
     *
     * @var string
     */
    private const CIPHER = 'aes-256-cbc';

    /**
     * Hash function
     *
     * @var string
     */
    private const HASH_FUNCTION = 'sha256';

    private static $secret = null;

    /**
     * Set secret for encryption/decryption
     *
     * @param $secret
     */
    public static function setSecret($secret)
    {
        self::$secret = $secret;
    }

    /**
     * Encrypt a string.
     *
     * @access public
     * @static static method
     * @param string $plainText
     * @return string
     */
    public static function encrypt(string $plainText)
    {
        // generate initialization vector,
        // this will make $iv different every time,
        // so, encrypted string will be also different.
        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_size);

        // generate key for authentication using ENCRYPTION_KEY & HMAC_SALT
        $key = mb_substr(hash(self::HASH_FUNCTION, self::$secret), 0, 32, '8bit');

        // append initialization vector
        $encrypted_string = openssl_encrypt($plainText, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        $ciphertext = $iv . $encrypted_string;

        // apply the HMAC
        $hmac = hash_hmac(self::HASH_FUNCTION, $ciphertext, $key);

        return base64_encode($hmac . $ciphertext);
    }

    /**
     * Decrypted a string.
     *
     * @access public
     * @static static method
     * @param string $cipherText
     * @return string
     * @throws Exception If $ciphertext is empty, or If functions don't exists
     */
    public static function decrypt(string $cipherText)
    {
        if (empty($ciphertext)) {
            throw new Exception('The String to decrypt can\'t be empty');
        }

        $ciphertext = base64_decode($ciphertext);
        // generate key used for authentication using ENCRYPTION_KEY & HMAC_SALT
        $key = mb_substr(hash(self::HASH_FUNCTION, self::$secret), 0, 32, '8bit');

        // split cipher into: hmac, cipher & iv
        $hmac = mb_substr($ciphertext, 0, 64, '8bit');
        $iv_cipher = mb_substr($ciphertext, 64, null, '8bit');

        // generate original hmac & compare it with the one in $ciphertext
        $originalHmac = hash_hmac(self::HASH_FUNCTION, $iv_cipher, $key);
        if (!hash_equals($hmac, $originalHmac)) {
            return false;
        }

        // split out the initialization vector and cipher
        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv = mb_substr($iv_cipher, 0, $iv_size, '8bit');
        $cipher = mb_substr($iv_cipher, $iv_size, null, '8bit');

        return openssl_decrypt($cipher, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Get a JWT token
     *
     * @param int $userId
     * @param array $payloads
     * @return string
     */
    public static function jwtByUser(int $userId, array $payloads = []): string
    {
        $payloads['id'] = $userId;
        $payloads['sub'] = (new DateTime())->getTimestamp();
        $header = json_encode(['alg' => self::HASH_FUNCTION, 'typ' => 'JWT']);
        $payload = json_encode($payloads);
        $signature = hash_hmac(self::HASH_FUNCTION, $header . $payload, self::$secret);
        return base64_encode($header) . "." . base64_encode($payload) . "." . $signature;
    }

    /**
     * Get a JWT content or null if empty
     *
     * @param string $jwt
     * @return array|null
     */
    public static function userByJwt(string $jwt): ?array
    {
        $parts = explode(".", $jwt);
        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signatureTest = hash_hmac(self::HASH_FUNCTION, $header . $payload, self::$secret);
        if ($signatureTest === $parts[2]) {
            return json_decode($payload, true);
        }
        return null;
    }

    /**
     * Crypt a content to sha256
     * @param string $content
     * @return string
     */
    public static function sha256(string $content): string
    {
        if (self::$secret) {
            return hash_hmac(self::HASH_FUNCTION, $content, self::$secret);
        } else {
            return hash(self::HASH_FUNCTION, $content);
        }
    }
}