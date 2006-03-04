<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is SQL stuff for Ukolovnik
// Copyright (c) 2005-2006 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/locale.php');
require_once('./lib/html.php');

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
