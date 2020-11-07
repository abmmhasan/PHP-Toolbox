<?php

use AbmmHasan\Toolbox\Helper\Collection;

if (!function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param array $array
     * @return Collection
     */
    function collect(array $array)
    {
        return new Collection($array);
    }
}