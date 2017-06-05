<?php
/**
 * functions.php
 * Project: yaIPAM
 * User: ktammling
 * Date: 27.05.17
 * Time: 13:39
 */

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row) {
                $tmp[$key] = $row[$field];
            }
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function in_multiarray($elem, $array)
{
    while (current($array) !== false) {
        if (current($array) == $elem) {
            return true;
        } elseif (is_array(current($array))) {
            if (in_multiarray($elem, current($array))) {
                return true;
            }
        }
        next($array);
    }
    return false;
}
