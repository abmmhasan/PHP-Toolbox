<?php


namespace AbmmHasan\Toolbox\Storage;


use AbmmHasan\Toolbox\Helper\Arr;

final class Path
{
    private static $path;
    private static $instance;

    /**
     * Set or Get formatted location by given path constant
     *
     * @param $name
     * @param $params
     * @return array|bool|string
     */
    public static function __callStatic($name, $params)
    {
        if ($name == 'set') {
            if(Arr::isSequential($params[0])){
                throw new \InvalidArgumentException("Invalid parameter!");
            }
            self::$path = $params[0];
            return true;
        }
        if (!isset(self::$path[$name])) {
            throw new \InvalidArgumentException("Invalid source {$name}");
        }
        return self::_resolvePath($name, $params);
    }

    private static function _resolvePath($name, $params)
    {
        $wanted = (self::$instance)->path[$name];
        $count = count($params);
        if ($count === 0) {
            return self::_resolveDS($wanted);
        } elseif ($count === 1) {
            return self::_resolveDS($wanted . DIRECTORY_SEPARATOR . $params[0]);
        } else {
            $allPath = [];
            foreach ($params as $param) {
                $allPath[] = self::_resolveDS($wanted . DIRECTORY_SEPARATOR . $param);
            }
            return $allPath;
        }
    }

    private static function _resolveDS($path)
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR;
    }
}
