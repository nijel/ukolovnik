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
    return strftime(_('%d.%m.%Y, %H:%M'), $date);
}

/**
 * Make links in text clickable.
 */
function STRING_find_links($text) {
    return preg_replace('@((http|ftp|https)://[a-z0-9A-Z.,?&;/=+_~#$%\@:-]+)([^.,]|$)@', '<a href="\1">\1</a>\3', htmlspecialchars($text));
}

/**
 * Quoted printable encoding.
 */
function STRING_quoted_printable($input) {
    // If imap_8bit() is available, use it.
    if (function_exists('imap_8bit')) {
        return imap_8bit($input);
    }

    // Rather dumb replacment: just encode everything.
    $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                 'A', 'B', 'C', 'D', 'E', 'F');

    $output = '';
    $len = strlen($input);
    for ($i = 0; $i < $len; ++$i) {
        $c = substr($input, $i, 1);
        $dec = ord($c);
        $output .= '=' . $hex[floor($dec / 16)] . $hex[floor($dec % 16)];
        if (($i + 1) % 25 == 0) {
            $output .= "=\r\n";
        }
    }
    return $output;
}

/**
 * Converts timestamp to vCalendar format.
 */
function STRING_format_date_vcal($value) {
    return sprintf('%04d%02d%02dT%02d%02d%02d',
        date('Y', $value),
        date('n', $value),
        date('j', $value),
        date('G', $value),
        date('i', $value),
        date('s', $value));
}

?>
