<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is toolbar for Ukolovnik
// Copyright (c) 2005-2006 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/html.php');

HTML_toolbar(array(
    'Main' => 'index.php',
    'Add' => 'index.php?cmd=add',
    'Categories' => 'index.php?cmd=cat',
    'AddCategory' => 'index.php?cmd=addcat',
//    'Export' => 'index.php?cmd=export', // not yet implemented
    'Stats' => 'index.php?cmd=stats',
    'Settings' => 'setup.php',
    ));
?>
