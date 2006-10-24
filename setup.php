<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is setup file for Ukolovnik
// Copyright (c) 2005-2006 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libaries
require_once('./lib/version.php');
require_once('./lib/http.php');
require_once('./lib/html.php');
require_once('./lib/config.php');
require_once('./lib/string.php');
require_once('./lib/sql.php');
require_once('./lib/category.php');
require_once('./lib/priority.php');
require_once('./lib/extensions.php');

HTTP_clean_request();

// Grab some parameters
if (empty($_REQUEST['cmd'])) {
    $cmd = 'list';
} else {
    $cmd = $_REQUEST['cmd'];
}

// Include correct language file
$failed_lang = LOCALE_init();

HTTP_nocache_headers();

HTML_header();

// Check for extensions
$check = EXTENSIONS_check();

if (count($check) > 0) {
    foreach($check as $name) {
        HTML_message('error', sprintf(LOCALE_get('ExtensionNeeded'), $name));
    }
    HTML_footer();
}


// Connect to database
if (!SQL_init()) {
    HTML_die_error(LOCALE_get('CanNotConnect'));
}

// Check with possible upgrade
SQL_check(true);

// We're done for now
HTML_message('notice', str_replace('index.php', '<a href="index.php">index.php</a>', LOCALE_get('TablesUpdated')));

// End
HTML_footer();
?>
