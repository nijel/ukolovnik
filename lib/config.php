<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is configuration handling stuff for Ukolovnik
// Copyright (c) 2005-2006 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/sql.php');

// Read config
require('./config.php');

// FIXME: do some sanity checks here

/**
 * Read value from configuration.
 * @param string name
 * @param string default value
 * @param string parameter storage (db or file)
 */
function CONFIG_get($name, $default = '', $source = 'db') {
    if ($source == 'file') {
        if (isset($GLOBALS[$name])) {
            return $GLOBALS[$name];
        } else {
            return $default;
        }
    } else {
        $value = $default;
        $q = SQL_do('SELECT `value` FROM `' . SQL_name('settings') . '` WHERE `key`="' . $name . '"');
        if (mysql_num_rows($q) > 0) {
            $row = mysql_fetch_assoc($q);
            $value = $row['value'];
        }
        mysql_free_result($q);
        return $value;
    }
}

/**
 * Sets value to (database) configuration.
 * @param string name
 * @param string value
 */
function CONFIG_set($name, $value) {
    SQL_do('REPLACE INTO `' . SQL_name('settings') . '` VALUES("' . $name . '", "' . addslashes($value) . '")');
}
?>
