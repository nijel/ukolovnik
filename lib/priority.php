<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is priority handling stuff for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/locale.php');

/**
 * Creates list of priorities.
 */
function PRIORITY_grab() {
    global $priorities;
    $priorities = array(LOCALE_get('Priority0'), LOCALE_get('Priority1'), LOCALE_get('Priority2'));
}
?>
