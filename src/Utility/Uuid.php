<?php


namespace AbmmHasan\Toolbox\Utility;


use AbmmHasan\Toolbox\Security\Random;

final class Uuid
{
    private static $nsList = [
        'dns', 'url', 'oid', 'x500'
    ];

    /**
     * Generate UUID v3 string
     *
     * @param string|null $string
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public static function v3(string $string = null, string $namespace = 'x500')
    {
        $ns = self::nsResolve($namespace);
        if (!$ns) {
            throw new \Exception('Invalid NameSpace!');
        }
        $string = $string ?? Random::string(32);
        $hash = md5(hex2bin($ns) . $string);
        return self::output(3, $hash);
    }

    /**
     * Generate UUID v4 Random string
     *
     * @return string
     */
    public static function v4()
    {
        $string = Random::string(32);
        return self::output(4, $string);
    }

    /**
     * Generate UUID v5 string
     *
     * @param string|null $string
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public static function v5(string $string = null, string $namespace = 'x500')
    {
        $ns = self::nsResolve($namespace);
        if (!$ns) {
            throw new \Exception('Invalid NameSpace!');
        }
        $string = $string ?? Random::string(32);
        $hash = sha1(hex2bin($ns) . $string);
        return self::output(5, $hash);
    }

    private static function output(int $version, string $string)
    {
        $string = str_split($string, 4);
        return sprintf("%08s-%04s-{$version}%03s-%04x-%012s",
            $string[0] . $string[1], $string[2],
            substr($string[3], 1, 3),
            hexdec($string[4]) & 0x3fff | 0x8000,
            $string[5] . $string[6] . $string[7]
        );
    }

    private static function nsResolve($namespace)
    {
        $ns = str_replace(['namespace', 'ns', '_'], '', strtolower($namespace));
        if ($type = array_search($ns, self::$nsList)) {
            return "6ba7b81{$type}9dad11d180b400c04fd430c8";
        }
        if (self::isValid($namespace)) {
            return str_replace(['-', '{', '}'], '', $namespace);
        }
        return false;
    }

    private static function isValid($uuid)
    {
        return (bool)preg_match('{^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$}Di', $uuid);
    }
}