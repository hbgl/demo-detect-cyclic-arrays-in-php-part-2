<?php

/**
 * Check if array is cyclic or not by checking if recursively
 * counting raises a warning. Works great. Use it.
 * @param array<mixed> $array
 * @return bool
 */
function is_cyclic_count(&$array)
{
    $isRecursive = false;
    set_error_handler(function ($errno, $errstr) use (&$isRecursive) {
        $isRecursive = $errno === E_WARNING && mb_stripos($errstr, 'recursion');
    });
    try {
        count($array, COUNT_RECURSIVE); /** @phpstan-ignore-line */
    } finally {
        restore_error_handler();
    }
    return $isRecursive;
}

/**
 * Check if an array is cyclic or not by adding markers. This function does
 * not work for all arrays. Do not use it.
 * @param array<mixed> $array
 * @param int $max_depth
 * @return bool
 */
function is_cyclic_marker(&$array, $max_depth = -1)
{
    $lastKey = array_key_last($array);
    if ($lastKey === null) {
        // Array is empty
        return false;
    }
    static $marker;
    if ($marker === null) {
        $marker = new stdClass();
    }
    if ($array[$lastKey] === $marker) {
        return true;
    }
    if ($max_depth === 0) {
        throw new Exception('Maximum nesting level exceeded.');
    }
    $array[] = $marker;
    foreach ($array as &$item) {
        if (is_array($item) && is_cyclic_marker($item, $max_depth - 1)) {
            array_pop($array);
            return true;
        }
    }
    array_pop($array);
    return false;
}

/**
 * Check if array is cyclic or not by using json_encode. It works but is
 * slow for large arrays.
 * @param array<mixed> $array
 * @return bool
 */
function is_cyclic_json_encode(array &$array)
{
    json_encode($array);
    return json_last_error() === JSON_ERROR_RECURSION;
}
