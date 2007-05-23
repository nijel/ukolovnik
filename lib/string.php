<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is string processing stuff for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/locale.php');

/**
 * Format date to string.
 */
function STRING_format_date($date) {
    return strftime(LOCALE_get('datefmt'), $date);
}

/**
 * Make links in text clickable.
 */
function STRING_find_links($text) {
    return preg_replace('@((http|ftp|https)://[a-z0-9A-Z.,?&;/=+_~#$%\@:-]+)([^.,]|$)@', '<a href="\1">\1</a>\3', htmlspecialchars($text));
}
?>
