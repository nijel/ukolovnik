<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is translation handling stuff for Ukolovnik
// Copyright (c) 2005-2006 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/config.php');

$locale = array();

/**
 * Initializes locales and loads translation.
 */
function LOCALE_init() {
    global $locale;

    $language = CONFIG_get('language', 'en');

    // Include correct language file
    if (file_exists('./languages/' . $language . '.php')) {
        require('./languages/' . $language . '.php');
        return FALSE;
    } else {
        require('./languages/en.php');
        return TRUE;
    }
}

/**
 * Returns translation for message.
 */
function LOCALE_get($name) {
    return $GLOBALS['locale'][$name];
}
?>
