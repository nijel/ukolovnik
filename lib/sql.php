<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is SQL stuff for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/locale.php');
require_once('./lib/html.php');

$db = NULL;
$required_tables = array('tasks', 'categories', 'settings');

/**
 * Initializes SQL connection.
 */
function SQL_init() {
    global $db;
    if ($db != NULL) {
        return TRUE;
    }
    // Connect to database
    $db = @mysql_connect(
        CONFIG_get('db_server', 'file'),
        CONFIG_get('db_user', 'file'),
        CONFIG_get('db_password', 'file'));
    if ($db === FALSE) {
        return FALSE;
    }

    // Is there need to handle charset?
    $q = SQL_do('SELECT VERSION()');
    if (mysql_num_rows($q) == 0) {
        return FALSE;
    }
    $r = mysql_fetch_array($q);
    mysql_free_result($q);
    $mysql_ver = explode('.', $r[0]);
    unset($r);
    if (!isset($mysql_ver[0]) || !isset($mysql_ver[1])) {
        return FALSE;
    }
    // Since MySQL 4 we use utf-8:
    if ($mysql_ver[0] >= 5 || ($mysql_ver[0] == 4 && $mysql_ver[1] >= 1)) {
        SQL_do('SET NAMES utf8');
    }
    unset($mysql_ver);
    return TRUE;
}

function SQL_postinit() {
}

/**
 * Rename table according to configured prefix.
 */
function SQL_name($tbl) {
    return CONFIG_get('table_prefix', 'file') . $tbl;
}

/**
 * Checks whether database is correct.
 */
function SQL_check_db($name) {
    global $db;
    return mysql_select_db($name, $db);
}

$SQL_check = NULL;

/**
 * Check for whether tables and databases are up to date. Optionally this 
 * can also update everything to currently required state.
 */
function SQL_check($upgrade = false) {
    global $db, $required_tables, $SQL_check;

    // If we already did check
    if ($SQL_check != NULL && !$upgrade) return $SQL_check;

    // Connect to database
    $dbname = CONFIG_get('db_database', 'file');
    if (!SQL_check_db($dbname)) {
        if ($upgrade) {
            SQL_do('CREATE DATABASE `' . $dbname . '`');
            HTML_message('notice', sprintf(_('Database %s has been created.'), htmlspecialchars($dbname)));
            SQL_check_db($dbname);
        } else {
            return array('db');
        }
    }

    $result = array();

    // Check tables
    foreach ($required_tables as $tbl) {
        $q = SQL_do('SHOW TABLES LIKE "' . SQL_name($tbl) . '"');
        if (mysql_num_rows($q) == 0) {
            if ($upgrade) {
                switch ($tbl) {
                    case 'tasks':
                        SQL_do('CREATE TABLE `' . SQL_name('tasks') . '` (
                                  `id` int(11) NOT NULL auto_increment,
                                  `category` int(11) NOT NULL,
                                  `priority` int(11) NOT NULL,
                                  `title` varchar(200) collate utf8_unicode_ci NOT NULL,
                                  `description` text collate utf8_unicode_ci NOT NULL,
                                  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
                                  `updated` timestamp NULL default NULL,
                                  `closed` timestamp NULL default NULL,
                                  `update_count` bigint default 0,
                                  PRIMARY KEY  (`id`),
                                  KEY `category` (`category`),
                                  KEY `priority` (`priority`)
                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
                        HTML_message('notice', sprintf(_('Table %s has been created.'), htmlspecialchars(SQL_name('tasks'))));
                        break;
                    case 'categories':
                        SQL_do('CREATE TABLE `' . SQL_name('categories') . '` (
                                  `id` int(11) NOT NULL auto_increment,
                                  `name` varchar(200) collate utf8_unicode_ci NOT NULL,
                                  `personal` tinyint(1) NOT NULL,
                                  PRIMARY KEY  (`id`),
                                  KEY `personal` (`personal`)
                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
                        HTML_message('notice', sprintf(_('Table %s has been created.'), htmlspecialchars(SQL_name('categories'))));
                        break;
                    case 'settings':
                        SQL_do('CREATE TABLE `' . SQL_name('settings') . '` (
                                  `key` varchar(200) collate utf8_unicode_ci NOT NULL,
                                  `value` varchar(200) collate utf8_unicode_ci NOT NULL,
                                  PRIMARY KEY  (`key`)
                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
                        HTML_message('notice', sprintf(_('Table %s has been created.'), htmlspecialchars(SQL_name('categories'))));
                        break;
                    default:
                        HTML_die_error('Table not defined: ' . $tbl);
                        break;
                }
            }
            $result[] = $tbl;
        }
        if ($q) mysql_free_result($q);
    }

    if (!in_array('settings', $result)) {
        // Check for settings version
        $ver = (int)CONFIG_get('version', 'db', true);
        // Set initial version information (don't care on $upgrade here, as this does not require any special privileges)
        if ($ver == 0) {
            CONFIG_set('version', '1', true);
            HTML_message('notice', sprintf(_('Settings database has been updated')));
        }
    }
    $ver = (int)CONFIG_get('version', 'db', true);
    if ($ver == 1) {
        if ($upgrade) {
            // Add update_count field
            SQL_do('ALTER TABLE `' . SQL_name('tasks') . '` ADD
                                  `update_count` bigint default 0');
            CONFIG_set('version', '2', true);
            HTML_message('notice', sprintf(_('Table %s updated.'), htmlspecialchars(SQL_name('tasks'))));
        } else {
            if (!isset($result['upgrade'])) {
                $result['upgrade'] = array();
            }
            $result['upgrade'][] = 'tasks';
        }
    }

    $SQL_check = $result;

    return $result;
}

/**
 * Execute SQL query and terminate script run if it fails.
 */
function SQL_do($query) {
    global $db;
    $q = mysql_query($query, $db);
    if ($q === FALSE) {
        echo mysql_error($db);
        HTML_die_error(sprintf(_('SQL query failed: %s'), htmlspecialchars($query)));
    }
    return $q;
}
?>
