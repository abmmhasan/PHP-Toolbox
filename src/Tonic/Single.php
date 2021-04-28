<?php


namespace AbmmHasan\Toolbox\Tonic;


trait Single
{
    use Common;

    private static $instance;

    /**
     * Creates a new instance of a singleton class (via late static binding),
     * accepting a variable-length argument list.
     *
     * @return self
     */
    final public static function instance(): Single
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
