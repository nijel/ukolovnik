<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is configuration handling stuff for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/sql.php');

// Read config
require('./config.php');

// FIXME: do some sanity checks here

/**
 * Default settings
 */

$default_settings = array(
    'db_server' => 'localhost',
    'db_user' => 'ukolovnik',
    'db_password' => 'ukolovnik',
    'db_database' => 'ukolovnik',
    'table_prefix' => 'ukolovnik_',
    'style' => 'default',
    'add_list' => '1',
    'add_stay' => '1',
    'language' => 'en',
    'version' => '0',
    );

/**
 * Read value from configuration.
 * @param string name
 * @param string default value
 * @param string parameter storage (db or file)
 */
function CONFIG_get($name, $source = 'db', $skip_check = false) {
    global $default_settings;

    if ($source == 'file') {
        if (isset($GLOBALS[$name])) {
            return $GLOBALS[$name];
        } else {
            return $default_settings[$name];
        }
    } else {
        $value = $default_settings[$name];
        /* This might be executed with wrong database configuration */
        if (!$skip_check && (!SQL_init() || count(SQL_check()) > 0)) {
            return $value;
        }
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
function CONFIG_set($name, $value, $skip_check = false) {
    if (!$skip_check && (!SQL_init() || count(SQL_check()) > 0)) {
        return;
    }
    SQL_do('REPLACE INTO `' . SQL_name('settings') . '` VALUES("' . $name . '", "' . addslashes($value) . '")');
}
?>
