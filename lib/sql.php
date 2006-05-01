<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is SQL stuff for Ukolovnik
// Copyright (c) 2005-2006 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/locale.php');
require_once('./lib/html.php');

$db = NULL;
$required_tables = array('tasks', 'categories');

function SQL_init() {
    global $db;
    // Connect to database
    $db = @mysql_connect(CONFIG_get('db_server'), CONFIG_get('db_user'), CONFIG_get('db_password'));
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

function SQL_name($tbl) {
    return CONFIG_get('table_prefix') . $tbl;
}

function SQL_check() {
    global $db, $required_tables;

    // Connect to database
    if (!mysql_select_db(CONFIG_get('db_database'), $db)) {
        return array('db');
    }

    $result = array();

    // Check tables
    foreach ($required_tables as $tbl) {
        $q = SQL_do('SHOW TABLES LIKE "' . SQL_name($tbl) . '"');
        if (mysql_num_rows($q) == 0) {
            $result[] = $tbl;
        }
        if ($q) mysql_free_result($q);
    }

    return $result;
}

function SQL_do($query) {
    global $db;
    $q = mysql_query($query, $db);
    if (!$q) {
        echo mysql_error($db);
        HTML_die_error(sprintf(LOCALE_get('SQLFailed'), htmlspecialchars($query)));
    }
    return $q;
}
?>
