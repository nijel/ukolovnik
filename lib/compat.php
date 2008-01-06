<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is compatibility stuff for Ukolovnik, it might be dropped in future when we can require newer php
// Copyright © 2005 - 2008 Michal Čihař
// Published under GNU GPL version 2


/**
 * calls $function vor every element in $array recursively
 *
 * @param   array   $array      array to walk
 * @param   string  $function   function to call for every array element
 */
function arrayWalkRecursive(&$array, $function) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayWalkRecursive($array[$key], $function);
        } else {
            $array[$key] = $function($value);
        }
    }
}

?>
