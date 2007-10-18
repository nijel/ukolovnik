<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is translation handling stuff for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/config.php');

/**
 * Path where are stored generated locales. You can generate them using
 * ./admin/locales-generate script.
 */
$locale_path = './locale-data/';

/**
 * Initializes locales and loads translation.
 */
function LOCALE_init() {
    global $locale_path;

    $language = CONFIG_get('language');

    if ($language == 'cs') {
        setlocale(LC_MESSAGES,  'cs_CZ.UTF-8');
    } elseif ($language == 'en') {
        setlocale(LC_MESSAGES, 'C');
    }

    $domain = 'ukolovnik';

    bindtextdomain($domain, $locale_path);
    textdomain($domain);
    bind_textdomain_codeset($domain, 'UTF-8');
}

/**
 * Lists available locales.
 */
function LOCALE_list() {
    global $locale_path;

    $d = opendir($locale_path);
    $langs = array('en' => 'en');
    if ($d) {
        while (($file = readdir($d)) !== false) {
            $matches = array();
            if (preg_match('/([a-zA-Z]{2,2})/', $file, $matches)) {
                $langs[$matches[1]] = $matches[1];
            }
        }
        closedir($d);
    }
    return $langs;
}
?>
