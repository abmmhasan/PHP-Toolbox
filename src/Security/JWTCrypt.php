<?php


namespace AbmmHasan\Toolbox\Security;


use Exception;

final class JWTCrypt
{
    private $secret = '';

    private $payload;

    /**
     * Constructor: Set Secret
     *
     * @param string $secret Secret string to encrypt with
     */
    public function __construct(string $secret)
    {
        $this->secret = $secret;
        $this->payload['iat'] = time();
    }

    /**
     * Register predefined  JWT keys (claims)
     *
     * https://tools.ietf.org/html/rfc7519#page-9
     *
     * @param string $issuer the name or identifier of the issuer
     * @param string $audience Specify the audience of the JWT as csv
     * @param string $subject Type of JWT payload, local/global identifier for what this JWT is for
     * @param string|null $key a unique string, which could be used to validate token
     * @return string
     * @throws Exception
     */
    public function registerClaims(string $issuer, string $audience, string $subject, string $key = null): string
    {
        $this->payload['iss'] = $issuer;
        $this->payload['aud'] = $audience;
        $this->payload['sub'] = $subject;
        return $this->payload['jti'] = $key ?? Random::string();
    }

    /**
     * Register predefined JWT keys (time based)
     *
     * https://tools.ietf.org/html/rfc7519#page-9
     *
     * @param int $notBefore a timestamp of when the token should start being considered valid.
     * @param int $expirationTime a timestamp of when the token should cease to be valid.
     * @throws Exception
     */
    public function registerTime(int $notBefore, int $expirationTime)
    {
        if ($notBefore < $this->payload['iat']) {
            throw new Exception("Invalid 'notBefore' value! Should be >= Current time.");
        }
        if ($expirationTime < $this->payload['iat'] || $expirationTime <= $notBefore) {
            throw new Exception("Invalid 'expirationTime' value! Should be, >= Current time & <= 'notBefore' time.");
        }
        $this->payload['nbf'] = $notBefore;
        $this->payload['exp'] = $expirationTime;
    }

    /**
     * Get JWT token for a given payload
     *
     * @param $payload
     * @param bool $encodedSignature signature should be base64 encoded
     * @return string
     * @throws Exception
     */
    public function getToken($payload, bool $encodedSignature = true): string
    {
        if (count($this->payload) !== 7) {
            throw new Exception('Please, register predefined payload values first!');
        }
        $this->payload += (array)$payload;
        $header = json_encode(['alg' => 'HS512', 'typ' => 'JWT']);
        $payload = json_encode($this->payload);
        $signature = hash_hmac('SHA512', $header . $payload, $this->secret);
        if ($encodedSignature) {
            $signature = trim(base64_encode($signature), '=');
        }
        return trim(base64_encode($header), '=') . "." . trim(base64_encode($payload), '=') . "." . $signature;
    }

    /**
     * Get verified content
     *
     * @param $token
     * @param bool $encodedSignature signature is base64 encoded
     * @return array
     */
    public function getContent($token, bool $encodedSignature = true): array
    {
        $parts = explode(".", $token);
        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signature = hash_hmac('SHA512', $header . $payload, $this->secret);
        if ($encodedSignature) {
            $signature = trim(base64_encode($signature), '=');
        }
        if ($signature === $parts[2]) {
            $payload = json_decode($payload, true);
            $now = time();
            if ($payload['iat'] <= $now && $payload['nbf'] <= $now && $payload['exp'] > $now) {
                return $payload;
            }
        }
    }
}
