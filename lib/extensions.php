<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is extension checking for Ukolovnik
// Copyright © 2005 - 2009 Michal Čihař
// Published under GNU GPL version 3 or later

$required_extensions = array('mysql' => 'mysql_connect', 'pcre' => 'preg_replace');

/**
 * Checks whethere required extensions are installed.
 */
function EXTENSIONS_check() {
    global $required_extensions;

    $result = array();

    foreach($required_extensions as $name => $function) {
        if (!function_exists($function)) {
            $result[] = $name;
        }
    }

    return $result;
}
?>
