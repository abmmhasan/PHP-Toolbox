<?php


namespace AbmmHasan\Toolbox\Security;


use Exception;

final class Random
{
    /**
     * Generate Secure random string of a given length
     *
     * @param int $length
     * @param $prefix
     * @param $postfix
     * @return false|string
     */
    public static function string($length = 32, $prefix = '', $postfix = '')
    {
        try {
            return $prefix .
                substr(
                    str_replace(['+', '/', '='], '', base64_encode(random_bytes($length))),
                    0, $length)
                . $postfix;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate Secure random number of given length
     *
     * @param int $length
     * @return false|int
     */
    public static function number($length = 6)
    {
        try {
            $min = 1 . str_repeat(0, $length - 1);
            $max = str_repeat(9, $length);
            return random_int((int)$min, (int)$max);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate random boolean
     *
     * @return bool
     * @throws Exception
     */
    public static function bool(): bool
    {
        return random_int(0, 1) === 1;
    }
}
