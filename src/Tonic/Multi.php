<?php


namespace AbmmHasan\Toolbox\Tonic;


trait Multi
{

    use Common;

    private static $instances = [];

    /**
     * Creates a new instance of a class flagged with a key.
     *
     * @param string $key the key which the instance should be stored/retrieved
     *
     * @return self
     */
    final public static function instance(string $key): Multi
    {
        if (!array_key_exists($key, self::$instances)) {
            self::$instances[$key] = new self;
        }
        return self::$instances[$key];
    }
}
