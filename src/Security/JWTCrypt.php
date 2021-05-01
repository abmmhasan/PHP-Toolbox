<?php


namespace AbmmHasan\Toolbox\Security;


use Exception;

class JWTCrypt
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
     * Register predefined  JWT keys
     *
     * https://tools.ietf.org/html/rfc7519#page-9
     *
     * @param string $iss the name or identifier of the issuer
     * @param string $aud Specify the audience of the JWT as csv
     * @param string $sub Type of JWT payload, local/global identifier for what this JWT is for
     * @param string|null $key a unique string, which could be used to validate token
     * @return string
     * @throws Exception
     */
    public function registerClaims(string $iss, string $aud, string $sub, string $key = null)
    {
        $this->payload['iss'] = $iss;
        $this->payload['aud'] = $aud;
        $this->payload['sub'] = $sub;
        return $this->payload['jti'] = $key ?? Random::string();
    }

    /**
     * Register predefined  JWT keys
     *
     * https://tools.ietf.org/html/rfc7519#page-9
     *
     * @param int $nbf a timestamp of when the token should start being considered valid.
     * @param int $exp a timestamp of when the token should cease to be valid.
     * @throws Exception
     */
    public function registerTime(int $nbf, int $exp)
    {
        if ($nbf < $this->payload['iat']) {
            throw new Exception("Invalid 'nbf' value! Should be >= Current time.");
        }
        if ($exp < $this->payload['iat'] || $exp < $nbf) {
            throw new Exception("Invalid 'exp' value! Should be >= Current time & 'nbf' time.");
        }
        $this->payload['nbf'] = $nbf;
        $this->payload['exp'] = $exp;
    }

    /**
     * Get JWT token for a given payload
     *
     * @param $payload
     * @return string
     * @throws Exception
     */
    public function getToken($payload): string
    {
        if (count($this->payload) !== 7) {
            throw new Exception('Please, register predefined payload values first!');
        }
        $this->payload += (array)$payload;
        $header = json_encode(['alg' => 'HS512', 'typ' => 'JWT']);
        $payload = json_encode($this->payload);
        $signature = hash_hmac('SHA512', $header . $payload, $this->secret);
        return trim(base64_encode($header), '=') . "." . trim(base64_encode($payload), '=') . "." . $signature;
    }

    /**
     * Get verified content
     *
     * @param $token
     * @return array
     */
    public function getContent($token): array
    {
        $parts = explode(".", $token);
        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signature = hash_hmac('SHA512', $header . $payload, $this->secret);
        if ($signature === $parts[2]) {
            return json_decode($payload, true);
        }
    }
}
