<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is translation handling stuff for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/config.php');

$locale = array();

/**
 * Initializes locales and loads translation.
 */
function LOCALE_init() {
    global $locale;

    $language = CONFIG_get('language', 'cs');

    if ($language == 'cs') {
        setlocale(LC_MESSAGES,  'cs_CZ.UTF-8');
    }

    $domain = 'ukolovnik';

    bindtextdomain($domain, './locale-data/');
    textdomain($domain);
    bind_textdomain_codeset($domain, 'UTF-8');
}
?>
