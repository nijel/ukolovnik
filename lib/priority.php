<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is priority handling stuff for Ukolovnik
// Copyright © 2005 - 2009 Michal Čihař
// Published under GNU GPL version 3 or later

// Grab needed libraries
require_once('./lib/locale.php');

/**
 * Creates list of priorities.
 */
function PRIORITY_grab() {
    global $priorities;
    $priorities = array(_('Low'), _('Medium'), _('High'));
}
?>
