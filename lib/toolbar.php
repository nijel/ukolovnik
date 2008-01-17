<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is toolbar for Ukolovnik
// Copyright © 2005 - 2008 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/html.php');

HTML_toolbar(array(
    _('Main') => 'index.php',
    _('Add') => 'index.php?cmd=add',
    _('Categories') => 'index.php?cmd=cat',
    _('Add category') => 'index.php?cmd=addcat',
    _('Export') => 'index.php?cmd=export', // not yet implemented
    _('Stats') => 'index.php?cmd=stats',
    _('Settings') => 'setup.php',
    _('About') => 'index.php?cmd=about',
    _('Donate') => 'http://cihar.com/donate',
    ));
?>
