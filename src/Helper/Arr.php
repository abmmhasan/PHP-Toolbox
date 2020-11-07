<?php

namespace AbmmHasan\Toolbox\Helper;

/**
 * Class Arr
 * @package AbmmHasan\Toolbox\Helper
 */
final class Arr
{
    /**
     * @var
     */
    private static $multi;

    /**
     * Reset the current array status(single or multi).
     * This is in use by several functions to deliver better performance
     *
     * @return void
     */
    public static function reset()
    {
        self::$multi = null;
    }

    /**
     * Custom multi array identifier Setter.
     * This is in use by several functions to deliver better performance
     *
     * @param bool $enable
     * @return void
     */
    public static function enableMulti($enable = true)
    {
        if ($enable) {
            self::$multi = true;
        } else {
            self::$multi = false;
        }
    }

    /**
     * Checks if array is multidimensional or not
     *
     * @param $array
     * @return bool
     */
    public static function isMulti($array)
    {
        return is_array($array) && count($array) !== count($array, COUNT_RECURSIVE);
    }


    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param mixed $value
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array $array
     * @param string|int $key
     * @return bool
     */
    public static function exists(array $array, $key)
    {
        return isset($array[$key]) || array_key_exists($key, $array);
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param $array
     * @param string|array $keys
     * @return bool
     */
    public static function has($array, $keys)
    {
        if (is_null($keys) || count($array) < 1) {
            return false;
        }
        if (is_string($keys) && self::exists($array, $keys)) {
            return true;
        }
        $keys = (array)$keys;
        if ($keys === []) {
            return false;
        }
        foreach ($keys as $key) {
            if (self::exists($array, $key)) {
                continue;
            }
            if (!self::segment($array, $key, false)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determine if any of the keys exist in an array using "dot" notation.
     *
     * @param \ArrayAccess|array $array
     * @param string|array $keys
     * @return bool
     */
    public static function hasAny($array, $keys)
    {
        if (empty($keys)) {
            return false;
        }

        $keys = (array)$keys;

        if (!$array) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (self::has($array, $key)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get item(s) from an array using "dot" notation.
     *
     * @param $array
     * @param string|int|array $keys
     * @param mixed $default
     * @return mixed
     */
    public static function get($array, $keys, $default = null)
    {
        if (is_array($keys)) {
            return self::getMany($array, $keys, $default);
        }
        return self::getValue($array, $keys, $default);
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param $array
     * @param string|array $keys
     * @param mixed $value
     * @return bool
     */
    public static function set(&$array, $keys, $value)
    {
        if (count($array) < 1) {
            return false;
        }
        $keys = is_array($keys) ? $keys : [$keys => $value];
        foreach ($keys as $key => $item) {
            self::setValue($array, $key, $item);
        }
        return true;
    }

    /**
     * Return the last element in an array.
     *
     * @param $array
     * @param mixed $default
     * @return mixed
     */
    public static function last($array, $default = null)
    {
        return empty($array) ? $default : end($array);
    }

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param $array
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function add(&$array, $key, $value)
    {
        if (is_null(self::get($array, $key))) {
            self::set($array, $key, $value);
            return true;
        }
        return false;
    }

    /**
     * Push an item onto the beginning of an array using "dot" notation
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     * @return array
     * @throws \Exception
     */

    public static function prepend(array &$array, $value, $key = null)
    {
        if (!is_null($key)) {
            $process = self::get($array, $key);
        } else {
            $process = $array;
        }
        if (!is_array($process)) {
            throw new \Exception('Invalid operation!');
        }
        array_unshift($process, $value);
        self::set($array, $key, $process);
        return $array;
    }

    /**
     * Add an item in the end of an array using "dot" notation
     * @param $array
     * @param $value
     * @param null|string $key
     */
    public static function append(&$array, $value, $key = null)
    {
        if (!is_null($key)) {
            $process = self::get($array, $key);
        } else {
            $process = $array;
        }
        $process[] = $value;
        self::set($array, $key, $process);
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function pull(array &$array, string $key, $default = null)
    {
        $value = self::get($array, $key, $default);
        static::forget($array, $key);
        return $value;
    }


    /**
     * Get a subset of the items from the given array.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function only(array $array, $keys)
    {
        if (self::isMulti($array)) {
            $result = [];
            foreach ($array as $item) {
                $result[] = array_intersect_key($item, array_flip((array)$keys));
            }
            return $result;
        }
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param $array
     * @param int $depth
     * @return array
     */
    public static function flatten($array, int $depth = INF)
    {
        $result = [];
        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : self::flatten($item, (int)$depth - 1);
                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param $array
     * @param string $prepend
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, self::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        return $results;
    }

    /**
     * Separate an array into two arrays. One with keys and the other with values.
     *
     * @param $array
     * @return array
     */
    public static function separate($array)
    {
        return ["keys" => array_keys($array), "values" => array_values($array)];
    }

    /**
     * Determines if an array is non-associative/sequential.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param $array
     * @return bool
     */
    public static function isSequential($array)
    {
        if (!self::exists($array, 0)) {
            return false;
        }
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param $array
     * @return bool
     */
    public static function isAssoc($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Cross join the given arrays, returning all possible permutations.
     *
     * @param array ...$arrays
     * @return array
     */
    public static function crossJoin(...$arrays)
    {
        $results = [[]];
        foreach ($arrays as $index => $array) {
            $append = [];
            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;
                    $append[] = $product;
                }
            }
            $results = $append;
        }
        return $results;
    }

    /**
     * @param $array
     * @param $keys
     * @param $default
     * @return array
     */
    private static function getMany($array, $keys, $default)
    {
        $config = [];
        foreach ($keys as $key => $value) {
            if (is_numeric($key)) {
                [$key, $value] = [$value, $default];
            }
            $config[$key] = self::getValue($array, $key, $value);
        }
        return $config;
    }

    /**
     * @param $array
     * @param $key
     * @param $default
     * @return array|mixed|null
     */
    private static function getValue($array, $key, $default)
    {
        if (!preg_match("/^[-_a-zA-Z0-9\.]*$/", $key)) {
            return null;
        }
        if (!is_array($array)) {
            return $default;
        }
        if (is_null($key)) {
            return $array;
        }
        if (self::exists($array, $key)) {
            return $array[$key];
        }
        if (strpos($key, '.') === false) {
            return $array[$key] ?? $default;
        }
        return self::segment($array, $key, $default);
    }

    /**
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    private static function setValue(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
    }

    /**
     * @param $array
     * @param $key
     * @param $default
     * @return mixed
     */
    private static function segment($array, $key, $default)
    {
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && self::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        return $array;
    }

    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function except(array $array, $keys)
    {
        self::forget($array, $keys);
        return $array;
    }


    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    public static function forget(array &$array, $keys)
    {
        $original = &$array;
        $keys = (array)$keys;
        if (count($keys) === 0) {
            return;
        }
        foreach ($keys as $key) {
            if (self::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }
            $parts = explode('.', $key);
            $array = &$original;
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get only Positive numeric values from an array
     *
     * @param $array
     * @return array
     */
    public static function positive($array)
    {
        $callback = function ($v) {
            return (is_numeric($v) && $v > 0) + 0;
        };
        return self::filter($array, $callback);
    }

    /**
     * Get only Negative numeric values from an array
     *
     * @param $array
     * @return array
     */
    public static function negative($array)
    {
        $callback = function ($v) {
            return (is_numeric($v) && $v < 0) + 0;
        };
        return self::filter($array, $callback);
    }

    /**
     * Checks if all the array values are integer
     *
     * @param array $array
     * @return bool
     */
    public static function isInt(array $array)
    {
        return $array === self::filter($array, 'is_int');
    }

    /**
     * Checks if all the array values are positive value
     *
     * @param array $array
     * @return bool
     */
    public static function isPositive(array $array)
    {
        if (min($array) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Checks if all the array values are negative
     *
     * @param array $array
     * @return bool
     */
    public static function isNegative(array $array)
    {
        if (max($array) < 0) {
            return true;
        }
        return false;
    }

    /**
     * Returns all the values of an array except null, false and empty strings
     *
     * @param array $array
     * @return array
     */
    public static function nonEmpty(array $array)
    {
        return array_values(self::filter($array, 'strlen'));
    }

    /**
     * Returns Array with given callback,
     * if no callback then returns non null, non false values (preserves keys)
     *
     * @param $array
     * @param $callback
     * @param null|String $column
     * @return array
     */
    public static function filter($array, $callback = null, $column = null)
    {
        $flag = null;
        if ($callback) {
            $flag = ARRAY_FILTER_USE_BOTH;
        }
        if (self::isMulti($array) && $column) {
            $process = $return = [];
            foreach ($array as $key => $value) {
                $process[$key] = $value[$column];
            }
            $process = array_filter($process, $callback, $flag);
            foreach ($process as $key => $value) {
                $return[$key] = $array[$key];
            }
            return $return;
        }
        return array_filter($array, $callback, $flag);
    }

    /**
     * Returns sum of values in an array
     *
     * @param $array
     * @param null|String $column
     * @return float|int
     */
    public static function sum($array, $column = null)
    {
        if (self::isMulti($array) && $column) {
            $array = array_column($array, $column);
        }
        return array_sum($array);
    }

    /**
     * Returns average of values in an array
     *
     * @param $array
     * @param null|String $column
     * @return float|int
     */
    public static function avg($array, $column = null)
    {
        if (self::isMulti($array) && $column) {
            $array = array_column($array, $column);
        }
        return (array_sum($array) / count($array));
    }

    /**
     * Returns product of values in an array
     *
     * @param $array
     * @param null|String $column
     * @return float|int
     */
    public static function prod($array, $column = null)
    {
        if (self::isMulti($array) && $column) {
            $array = array_column($array, $column);
        }
        return array_product($array);
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param array $array
     * @return array
     */
    public static function collapse(array $array)
    {
        $results = [];

        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }
            $results = array_merge($results, $values);
        }
        return $results;
    }

    /**
     * Chunk the array.
     *
     * @param $array
     * @param int $size
     * @return array
     */
    public static function chunk($array, int $size)
    {
        if ($size <= 0 || count($array) <= $size) {
            return $array;
        }
        $chunks = [];
        foreach (array_chunk($array, $size, true) as $chunk) {
            $chunks[] = $chunk;
        }
        return $chunks;
    }

    /**
     * Check if the array is unique
     *
     * @param $array
     * @param null|String $column
     * @return bool
     */
    public static function isUnique($array, $column = null)
    {
        if (self::isMulti($array) && $column) {
            $array = array_column($array, $column);
        }
        return count($array) === count(array_flip($array));
    }

    /**
     * Pluck Single Key(optional) and values from an array
     *
     * @param $array
     * @param $value
     * @param null|String $key
     * @return array
     */
    public static function pluck($array, $value, $key = null)
    {
        $results = [];
        $value = is_string($value) ? explode('.', $value) : $value;

        foreach ($array as $item) {
            $assign = [];
            foreach ($value as $getValue) {
                $assign[$getValue] = $item[$getValue];
            }
            if (is_null($key)) {
                $results[] = $assign;
            } else {
                $results[$item[$key]] = $assign;
            }
        }
        return $results;
    }

    /**
     * Generates an array by using one array for keys and another for its values
     * If arrays (keys and values) are of unequal length it will take minimal size in-between.
     *
     * @param $keys
     * @param $values
     * @return array|false
     */
    public static function combine(array $keys, array $values)
    {
        $keyCount = count($keys);
        $valueCount = count($values);
        if ($keyCount != $valueCount) {
            $size = ($keyCount > $valueCount) ? $valueCount : $keyCount;
            $keys = array_slice($keys, 0, $size);
            $values = array_slice($values, 0, $size);
        }
        return array_combine($keys, $values);
    }


    /**
     * Retrieve duplicate items from the collection.
     *
     * @param $array
     * @param null|String $column
     * @return array
     */
    public static function duplicates($array, $column = null)
    {
        $duplicates = array();
        if (self::isMulti($array) && $column) {
            $array = array_column($array, $column);
        }
        foreach (array_count_values($array) as $val => $c) {
            if ($c > 1) {
                $duplicates[] = $val;
            }
        }
        return $duplicates;
    }

    /**
     * Group an array by a field.
     *
     * @param $array
     * @param $groupBy
     * @param bool $preserveKeys
     * @return array
     */
    public static function groupBy($array, $groupBy, $preserveKeys = false)
    {
        $result = array();
        if ($preserveKeys) {
            foreach ($array as $key => $element) {
                if (!self::exists($result, $element[$groupBy])) {
                    $result[$element[$groupBy]]["count"] = 0;
                }
                $result[$element[$groupBy]]["count"]++;
                $result[$element[$groupBy]]["data"][$key] = $element;
            }
        } else {
            foreach ($array as $element) {
                if (!self::exists($result, $element[$groupBy])) {
                    $result[$element[$groupBy]]["count"] = 0;
                }
                $result[$element[$groupBy]]["count"]++;
                $result[$element[$groupBy]]["data"][] = $element;
            }
        }
        return $result;
    }

    /**
     * Count array by field
     *
     * @param $array
     * @param $countBy
     * @return array
     */
    public static function countBy($array, $countBy = null)
    {
        $result = array();
        if (self::isMulti($array) && $countBy) {
            foreach ($array as $key => $element) {
                if (self::exists($result, $element[$countBy])) {
                    $result[$element[$countBy]]["count"]++;
                    $result[$element[$countBy]]["indexes"][] = $key;
                    continue;
                }
                $result[$element[$countBy]]["count"] = 1;
                $result[$element[$countBy]]["indexes"][] = $key;
            }
        } else {
            foreach ($array as $key => $element) {
                if (self::exists($result, $element)) {
                    $result[$element]["count"]++;
                    $result[$element]["indexes"][] = $key;
                    continue;
                }
                $result[$element]["count"] = 1;
                $result[$element]["indexes"][] = $key;
            }
        }
        return $result;
    }

    /**
     * Return an array consisting of every n-th element.
     *
     * @param array $array
     * @param int $step
     * @param int $offset
     * @return array
     */
    public function nth(array $array, int $step, $offset = 0)
    {
        $return = [];
        $position = 0;
        foreach ($array as $item) {
            if ($position % $step === $offset) {
                $return[] = $item;
            }
            $position++;
        }
        return $return;
    }

    /**
     * Get the first element in an array.
     * or
     * Get the first item by the given key value pair.
     *
     * @param $array
     * @param null|String $key
     * @param mixed $operator
     * @param mixed $value
     * @return mixed
     */
    public static function first($array, $key = null, $operator = null, $value = null)
    {
        if ($value == null && $operator != null) {
            $value = $operator;
            $operator = null;
        }
        foreach ($array as $item) {
            if (!$key ||
                (!$value && self::exists($item, $key)) ||
                (self::exists($item, $key) && compare($item[$key], $value, $operator))) {
                return $item;
            }
        }
        return false;
    }


    /**
     * Returns a multi-dimensional array with given key-value filter
     *
     * @param $array
     * @param string $index | $operator
     * @param null|string $operator | $value
     * @param null|string $value
     * @return array
     */
    public static function where($array, string $index, $operator = null, $value = null)
    {
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = null;
        }
        $new = [];
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                if (self::exists($array[$key], $index) && compare($array[$key][$index], $value, $operator)) {
                    $new[$key] = $array[$key];
                }
            }
        }
        return $new;
    }

    /**
     * Filter items such that the value of the given key is between the given values.
     *
     * @param $array
     * @param string|int|float $index | $from
     * @param int|float $from | $to
     * @param null|int|float $to
     * @return array
     */
    public static function between($array, string $index, $from, $to = null)
    {
        if (self::isMulti($array)) {
            return self::where(
                self::where($array, $index, '>=', $from),
                $index,
                '<=',
                $to
            );
        }
        return self::where(
            self::where($array, '>=', $index),
            '<=',
            $from
        );
    }

    /**
     * "Paginate" the array by slicing it into a smaller array.
     *
     * @param $array
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function paginate($array, int $page, int $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);
        return array_slice($array, $offset, $perPage, true);
    }

    /**
     * Concatenate values of a given key as a string.
     *
     * @param $array
     * @param string $column | $glue
     * @param null|string $glue
     * @return string
     */
    public static function implode($array, string $column, $glue = null)
    {
        if (self::isMulti($array) && $column) {
            return implode($glue, array_column($array, $column));
        }
        return implode($column, $array);
    }

    /**
     * Get the min value of a given key.
     *
     * @param $array
     * @param null|string $column
     * @return mixed
     */
    public function min($array, $column = null)
    {
        if (self::isMulti($array) && $column) {
            return min(array_column($array, $column));
        }
        return min($array);
    }

    /**
     * Get the max value of a given key.
     *
     * @param $array
     * @param null|string $column
     * @return mixed
     */
    public function max($array, $column = null)
    {
        if (self::isMulti($array) && $column) {
            return max(array_column($array, $column));
        }
        return max($array);
    }

    /**
     * Get an array of all elements that pass a given test.
     *
     * @param $array
     * @param null|string $column
     * @param callable|mixed $callback
     * @return array
     */
    public static function accept($array, $column = null, $callback = true)
    {
        if (empty($callback) && !empty($column)) {
            $callback = $column;
            $column = null;
        }
        $useAsCallable = self::useAsCallable($callback);
        return self::filter(
            $array,
            function ($value) use ($callback, $useAsCallable) {
                return $useAsCallable
                    ? $callback($value)
                    : $value == $callback;
            },
            $column
        );
    }

    /**
     * Get an array of all elements that do not pass a given test.
     *
     * @param $array
     * @param null|string $column
     * @param callable|mixed $callback
     * @return array
     */
    public static function reject($array, $column = null, $callback = true)
    {
        if (empty($callback) && !empty($column)) {
            $callback = $column;
            $column = null;
        }
        $useAsCallable = self::useAsCallable($callback);
        return self::filter(
            $array,
            function ($value) use ($callback, $useAsCallable) {
                return $useAsCallable
                    ? !$callback($value)
                    : $value != $callback;
            },
            $column
        );
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value
     * @return bool
     */
    private static function useAsCallable($value)
    {
        return !is_string($value) && is_callable($value);
    }
}
