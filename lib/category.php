<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is category handling stuff for Ukolovnik
// Copyright © 2005 - 2013 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libraries
require_once('./lib/sql.php');

/**
 * Grab all categories.
 */
function CATEGORY_grab() {
    global $categories, $categories_pers, $categories_prof;

    $q = SQL_do('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'categories ORDER BY name');
    $categories = array();
    $categories_pers = array();
    $categories_prof = array();
    while ($row = mysql_fetch_assoc($q)) {
        $categories[$row['id']] = $row['name'];
        if ($row['personal']) {
            $categories_pers[$row['id']] = $row['name'];
        } else {
            $categories_prof[$row['id']] = $row['name'];
        }
    }
    mysql_free_result($q);
}

/**
 * Show edit box for category.
 */
function CATEGORY_show_edit($title, $cmd, $name, $personal, $id = NULL) {
    echo '<fieldset><legend>' . $title . '</legend><form method="post" action="index.php">';
    if (isset($id)) {
        echo '<input type="hidden" name="id" value="' . $id . '" \>';
    }
    echo '<label class="desc" for="in_name">' . _('Name') . '</label>';
    echo '<input type="text" id="in_name" name="name" maxlength="200" value="' . $name . '" />';
    echo '<input type="checkbox" id="ch_personal" name="personal" ' . $personal . '/>';
    echo '<label for="ch_personal">' . _('Personal') . '</label>';
    echo '<input type="hidden" name="cmd" value="' . $cmd . '" \>';
    echo '<input type="submit" value="' . $title . '"/></form></fieldset>';
}
?>
