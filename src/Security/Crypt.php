<?php

namespace AbmmHasan\Toolbox\Security;

use Exception;

class Crypt
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

    /**
     * Encrypt a string.
     *
     * @access public
     * @static static method
     * @param string $plain
     * @param $encryption_key
     * @param $salt
     * @return string
     * @throws Exception If functions don't exists
     */
    public static function encrypt(string $plain, $encryption_key, $salt)
    {
        // generate initialization vector,
        // this will make $iv different every time,
        // so, encrypted string will be also different.
        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_size);

        // generate key for authentication using ENCRYPTION_KEY & HMAC_SALT
        $key = mb_substr(hash(self::HASH_FUNCTION, $encryption_key . $salt), 0, 32, '8bit');

        // append initialization vector
        $encrypted_string = openssl_encrypt($plain, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        $ciphertext = $iv . $encrypted_string;

        // apply the HMAC
        $hmac = hash_hmac('sha256', $ciphertext, $key);

        return base64_encode($hmac . $ciphertext);
    }

    /**
     * Decrypted a string.
     *
     * @access public
     * @static static method
     * @param string $ciphertext
     * @param $encryption_key
     * @param $salt
     * @return string
     * @throws Exception If $ciphertext is empty, or If functions don't exists
     */
    public static function decrypt(string $ciphertext, $encryption_key, $salt)
    {
        if (empty($ciphertext)) {
            throw new Exception('The String to decrypt can\'t be empty');
        }

        $ciphertext = base64_decode($ciphertext);
        // generate key used for authentication using ENCRYPTION_KEY & HMAC_SALT
        $key = mb_substr(hash(self::HASH_FUNCTION, $encryption_key . $salt), 0, 32, '8bit');

        // split cipher into: hmac, cipher & iv
        $macSize = 64;
        $hmac = mb_substr($ciphertext, 0, $macSize, '8bit');
        $iv_cipher = mb_substr($ciphertext, $macSize, null, '8bit');

        // generate original hmac & compare it with the one in $ciphertext
        $originalHmac = hash_hmac('sha256', $iv_cipher, $key);
        if (!hash_equals($hmac, $originalHmac)) {
            return false;
        }

        // split out the initialization vector and cipher
        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv = mb_substr($iv_cipher, 0, $iv_size, '8bit');
        $cipher = mb_substr($iv_cipher, $iv_size, null, '8bit');

        return openssl_decrypt($cipher, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
    }
}