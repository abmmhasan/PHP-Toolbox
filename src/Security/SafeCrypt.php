<?php


namespace AbmmHasan\Toolbox\Security;


use Exception;

final class SafeCrypt
{
    private $secret = '';
    private $salt = '';
    private $iv = '';

    private $keyIterationCount = 10000;
    private $keyLength = 50;
    private $keyAlgo = 'SHA3-512';

    private $hmacAlgo = 'SHA3-512';
    private $sha2Len = 64;

    private $encryptionMethod = 'aes-256-cbc';

    /**
     * Constructor: Set Secret & Salt (& optionally IV string) for encryption/decryption
     *
     * @param string $secret Secret string to encrypt with
     * @param string $salt Salt string for hashing
     * @param string $iv IV string (if omitted IV will be generated automatically)
     */
    public function __construct(string $secret, string $salt, string $iv = '')
    {
        $this->secret = $secret;
        $this->salt = $salt;
        $this->iv = $iv;
    }

    /**
     * Set encryption method
     *
     * Default: AES-256-CBC
     *
     * @param string $method
     */
    public function setEncryptionMethod(string $method)
    {
        $this->encryptionMethod = $method;
    }

    /**
     * Set Encryption key property
     *
     * Default: Algorithm: SHA3-512, Length: 50, Iteration Count: 10000
     *
     * @param string $algorithm
     * @param int $length
     * @param int $iterationCount
     */
    public function setKeyProperty(string $algorithm, int $length, int $iterationCount)
    {
        $this->keyAlgo = $algorithm;
        $this->keyLength = $length;
        $this->keyIterationCount = $iterationCount;
    }

    /**
     * Set property for signature key, used for signing/verifying encryption
     *
     * @param string $algorithm
     * @param int $length
     */
    public function setSignatureProperty(string $algorithm, int $length)
    {
        $this->hmacAlgo = $algorithm;
        $this->sha2Len = $length;
    }

    /**
     * Get encryption key
     *
     * @return false|string
     */
    private function getKey()
    {
        return openssl_pbkdf2(
            $this->secret,
            $this->salt,
            $this->keyLength,
            $this->keyIterationCount,
            $this->keyAlgo
        );
    }

    /**
     * Calculate/Generate IV string
     *
     * @throws Exception
     */
    private function calculateIV()
    {
        $length = openssl_cipher_iv_length($this->encryptionMethod);
        if (!empty($this->iv) && ($found = mb_strlen($this->iv, '8bit')) != $length) {
            throw new Exception("IV length mismatch (Expected: $length, Found: $found)");
        }
        if (empty($this->iv) && $length > 0) {
            $this->iv = openssl_random_pseudo_bytes($length);
        }
    }

    /**
     * Encrypt a string
     *
     * @param string $string
     * @return string raw format
     * @throws Exception
     */
    public function encrypt(string $string): string
    {
        self::calculateIV();
        $encryptionKey = self::getKey();
        $cText = openssl_encrypt(
            $string,
            $this->encryptionMethod,
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $this->iv
        );
        return $this->iv . hash_hmac($this->hmacAlgo, $cText, $encryptionKey, true) . $cText;
    }

    /**
     * Decrypt a cypher text
     *
     * @param string $encryptedString raw format
     * @return false|string
     */
    public function decrypt(string $encryptedString)
    {
        $ivLen = openssl_cipher_iv_length($this->encryptionMethod);
        $cText = substr($encryptedString, $ivLen + $this->sha2Len);
        $encryptionKey = self::getKey();
        return hash_equals(
            substr($encryptedString, $ivLen, $this->sha2Len),
            hash_hmac($this->hmacAlgo, $cText, $encryptionKey, true)
        )
            ? openssl_decrypt(
                $cText,
                $this->encryptionMethod,
                $encryptionKey,
                OPENSSL_RAW_DATA,
                substr($encryptedString, 0, $ivLen))
            : false;
    }

    /**
     * Encrypt String
     *
     * @param string $string
     * @return string base64 encoded format
     * @throws Exception
     */
    public function encrypt64(string $string): string
    {
        return trim(base64_encode(self::encrypt($string)), '=');
    }

    /**
     * Decrypt a cypher text
     *
     * @param string $encryptedString base64 encoded format
     * @return false|string
     */
    public function decrypt64(string $encryptedString)
    {
        if (!$encryptedString = base64_decode($encryptedString, true)) {
            return false;
        }
        return self::decrypt($encryptedString);
    }

    /**
     * Encrypt String
     *
     * @param string $string
     * @return string hex encoded format
     * @throws Exception
     */
    public function encryptHex(string $string): string
    {
        return bin2hex(self::encrypt($string));
    }

    /**
     * Decrypt a cypher text
     *
     * @param string $encryptedString hex encoded format
     * @return false|string
     */
    public function decryptHex(string $encryptedString)
    {
        return self::decrypt(hex2bin($encryptedString));
    }
}
