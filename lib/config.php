<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is configuration handling stuff for Ukolovnik
// Copyright (c) 2005-2006 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries

// Read config
require('./config.php');

// FIXME: do some sanity checks here

/**
 * Read value from configuration.
 * @param string name
 * @param string default value
 */
function CONFIG_get($name, $default = '') {
    if (isset($GLOBALS[$name])) {
        return $GLOBALS[$name];
    } else {
        return $default;
    }
}
?>
