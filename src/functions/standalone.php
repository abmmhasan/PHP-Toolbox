<?php

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        switch (true) {
            case isset($_SERVER[$key]):
                $value = $_SERVER[$key];
                break;
            case isset($_ENV[$key]):
                $value = $_ENV[$key];
                break;
            case (function_exists('apache_getenv') && apache_getenv($key) !== false):
                $value = apache_getenv($key);
                break;
            default:
                $value = getenv($key);
                break;
        }
        if ($value === false) {
            return $default;
        }
        return value($value);
    }
}

if (!function_exists('value')) {
    /**
     * Return value in exact form
     *
     * @param $value
     * @return bool|string|void|null
     */
    function value($value)
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }
}

if (!function_exists('compare')) {
    /**
     * Get compared according to the operator.
     *
     * @param $retrieved
     * @param $value
     * @param string|null $operator
     * @return bool.
     */
    function compare($retrieved, $value, string $operator = null): bool
    {
        switch ($operator) {
            case '!=':
            case '<>':
                return $retrieved != $value;
            case '<':
                return $retrieved < $value;
            case '>':
                return $retrieved > $value;
            case '<=':
                return $retrieved <= $value;
            case '>=':
                return $retrieved >= $value;
            case '===':
                return $retrieved === $value;
            case '!==':
                return $retrieved !== $value;
            case '=':
            case '==':
            default:
                return $retrieved == $value;
        }
    }
}

if (!function_exists('is_blank')) {
    /**
     * Determine if the given value is "blank".
     *
     * @param mixed $value
     * @return bool
     */
    function is_blank($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (!function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param int $times
     * @param callable $callback
     * @param int $sleep
     * @param null $when
     * @return mixed
     *
     * @throws Exception
     */
    function retry(int $times, callable $callback, int $sleep = 0, $when = null)
    {
        $attempts = 0;
        $times--;

        beginning:
        $attempts++;

        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if (!$times || ($when && !$when($e))) {
                throw $e;
            }

            $times--;

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (!function_exists('formatBytes')) {
    /**
     * Get Human readable Byte format
     *
     * @param $bytes
     * @param int $precision
     * @return string
     *
     */
    function formatBytes($bytes, int $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('duration')) {
    /**
     * Get time spend for a process (in microseconds)
     *
     * @param bool / epoch time $from
     * @return mixed
     */
    function duration($from = false)
    {
        if ($from) {
            return microtime(true) - $from;
        }
        return microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
    }
}

if (!function_exists('httpDate')) {
    /**
     * Converts any recognizable date format to an HTTP date.
     *
     * @param mixed $date The incoming date value.
     * @return string A formatted date.
     * @throws Exception
     */
    function httpDate($date = null): string
    {
        if ($date instanceof \DateTime) {
            $date = \DateTimeImmutable::createFromMutable($date);
        } else {
            $date = new \DateTime($date);
        }

        try {
            $date->setTimeZone(new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            $date = new \DateTime('0001-01-01', new \DateTimeZone('UTC'));
        } finally {
            return $date->format('D, d M Y H:i:s') . ' GMT';
        }
    }
}
