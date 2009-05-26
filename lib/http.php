<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is HTTP handling stuff for Ukolovnik
// Copyright © 2005 - 2009 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/compat.php');

/**
 * Outputs headers to disable caching.
 */
function HTTP_nocache_headers() {
    // Used later
    $now = gmdate('D, d M Y H:i:s') . ' GMT';

    // General header for no caching
    header('Expires: ' . $now); // rfc2616 - Section 14.21
    header('Last-Modified: ' . $now);
    header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
    header('Pragma: no-cache'); // HTTP/1.0
}

/**
 * Outputs http header with content type.
 * @param string content type
 */
function HTTP_type_header($type) {
    header('Content-Type: ' . $type);
}

/**
 * Strips possible slashes from reqest.
 */
function HTTP_clean_request() {
    if (get_magic_quotes_gpc()) {
        arrayWalkRecursive($_REQUEST, 'stripslashes');
    }
}
?>
