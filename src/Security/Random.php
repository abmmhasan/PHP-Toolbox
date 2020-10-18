<?php


namespace AbmmHasan\Toolbox\Security;


class Random
{
    /**
     * Generate Secure random string of given length
     *
     * @param int $length
     * @param $prefix
     * @param $postfix
     * @return false|string
     */
    public static function string($length = 32, $prefix = '', $postfix = '')
    {
        try {
            return $prefix . strtoupper(bin2hex(random_bytes(ceil($length / 2)))) . $postfix;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate Secure random number of given length
     *
     * @param int $length
     * @return false|string
     * @throws \Exception
     */
    public static function number($length = 6)
    {
        try {
            $min = 1 . str_repeat(0, $length - 1);
            $max = str_repeat(9, $length);
            return random_int((int)$min, (int)$max);
        } catch (\Exception $e) {
            return false;
        }
    }

}