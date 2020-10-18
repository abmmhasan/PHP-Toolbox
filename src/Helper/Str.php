<?php

namespace AbmmHasan\Toolbox\Helper;

/**
 * Class Str
 * @package AbmmHasan\Toolbox\Helper
 */
class Str
{

    /**
     * Return the remainder of a string after a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function after(string $subject, string $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Return the remainder of a string after the last occurrence of a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function afterLast(string $subject, string $search)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, (string) $search);

        if ($position === false) {
            return $subject;
        }

        return substr($subject, $position + strlen($search));
    }

    /**
     * Get the portion of a string before a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function before(string $subject, string $search)
    {
        return $search === '' ? $subject : explode($search, $subject)[0];
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function beforeLast(string $subject, string $search)
    {
        if ($search === '') {
            return $subject;
        }

        if ($pos = mb_strrpos($subject, $search) === false) {
            return $subject;
        }

        return mb_substr($subject, 0, $pos, 'UTF-8');
    }

    /**
     * Get the portion of a string between two given values.
     *
     * @param string $subject
     * @param string $from
     * @param string $to
     * @return string
     */
    public static function between(string $subject, string $from, string $to)
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return self::beforeLast(self::after($subject, $from), $to);
    }

    /**
     * Determine if a given string contains all array values.
     *
     * @param string $haystack
     * @param string[] $needles
     * @return bool
     */
    public static function contains(string $haystack, array $needles)
    {
        foreach ($needles as $needle) {
            if ($needle === '' || mb_strpos($haystack, $needle) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function endsWith(string $haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $cap
     * @return string
     */
    public static function finish(string $value, string $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param string|array $pattern
     * @param string $value
     * @return bool
     */
    public static function is($pattern, string $value)
    {
        $patterns = Arr::wrap($pattern);

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limit(string $value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    /**
     * Get a wrapped/trimmed/sized version of a long string
     *
     * @param $string
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limitIntact($string, $limit = 100, $end = '...')
    {
        $string = html_entity_decode(strip_tags($string));
        if (strlen($string) > $limit) {
            return substr($string, 0, strrpos(substr($string, 0, $limit), ' ')) . $end;
        }
        return $string;
    }

    /**
     * Limit the number of words in a string.
     *
     * @param string $value
     * @param int $words
     * @param string $end
     * @return string
     */
    public static function words(string $value, $words = 100, $end = '...')
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);

        if (!isset($matches[0]) || mb_strlen($value) === mb_strlen($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]) . $end;
    }

    /**
     * Parse a Class@method style callback into class and method.
     *
     * @param string $callback
     * @param string|null $default
     * @return array
     */
    public static function parseCallback(string $callback, $default = null)
    {
        return mb_strpos($callback, '@') ?
            explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param $pattern
     * @param array $data
     * @param string $subject
     * @return string
     */
    public static function replaceArray($pattern, array $data, string $subject)
    {
        if (Arr::isAssoc($data)) {
            $data = Arr::separate($data);
            return str_replace($data['keys'], $data['values'], $subject);
        } elseif (@preg_match($pattern, null) !== false) {
            return self::preg_replace($pattern, $data, $subject);
        } else {
            return str_replace(array_fill(0, count($data), $pattern), $data, $subject);
        }
    }

    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $subject)
    {
        if ($search == '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $subject)
    {
        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function start(string $value, string $prefix)
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string $title
     * @param string $separator
     * @return string
     */
    public static function slug(string $title, $separator = '-')
    {
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator . 'at' . $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', strtolower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function startsWith(string $haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the number of substring occurrences.
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param int|null $length
     * @return int
     */
    public static function substrCount(string $haystack, string $needle, $offset = 0, $length = null)
    {
        if (! is_null($length)) {
            return substr_count($haystack, $needle, $offset, $length);
        } else {
            return substr_count($haystack, $needle, $offset);
        }
    }


    /**
     * Replace a given pattern with each value in the array sequentially.
     *
     * @param string $pattern
     * @param array $replacements
     * @param string $subject
     * @return string
     */
    public static function preg_replace(string $pattern, array $replacements, string $subject)
    {
        return preg_replace_callback(
            $pattern,
            function () use (&$replacements) {
                foreach ($replacements as $key => $value) {
                    return array_shift($replacements);
                }
            },
            $subject
        );
    }

    /**
     * Converts string to Hex Code
     *
     * @param $str
     * @return string
     */
    public static function hexEntities($str)
    {
        $return = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $return .= '&#x' . bin2hex(substr($str, $i, 1)) . ';';
        }
        return $return;
    }

    /**
     * Quote a string
     *
     * @param string $s
     * @return string
     */
    public static function quote(string $s): string
    {
        if (preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s)) {
            return $s;
        }

        return '"' . addcslashes($s, '"\\"') . '"';
    }

    /**
     * Unquote a string
     *
     * @param string $s
     * @return string
     */
    public static function unquote(string $s): string
    {
        return preg_replace('/\\\\(.)|"/', '$1', $s);
    }
}
