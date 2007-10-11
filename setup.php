<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is setup file for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
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

// Include correct language file
$failed_lang = LOCALE_init();

// Grab some parameters
if (empty($_REQUEST['cmd'])) {
    $cmd = '';
} else {
    $cmd = $_REQUEST['cmd'];
}

$langs = LOCALE_list();

$d = opendir('./styles/');
$styles = array();
if ($d) {
    while (($file = readdir($d)) !== false) {
        $matches = array();
        if (preg_match('/([a-zA-Z_-]*)\.css/', $file, $matches)) {
            $styles[$matches[1]] = $matches[1];
        }
    }
    closedir($d);
}

$settings = array(
    array('name' => 'language', 'text' => _('Language'), 'type' => 'select', 'values' => $langs),
    array('name' => 'style', 'text' => _('Style'), 'type' => 'select', 'values' => $styles),
    array('name' => 'add_stay', 'text' => _('Stay on add page after adding new entry'), 'type' => 'bool'),
    array('name' => 'add_list', 'text' => _('Show entries list on add page'), 'type' => 'bool'),
/*
    array('name' => '', 'text' => _(''), 'type' => '', 'values' => array('')),
*/
    );

// Process settings
if ($cmd == 'save') {
    foreach($settings as $val) {
        if (isset($_REQUEST['s_' . $val['name']])) {
            $data = $_REQUEST['s_' . $val['name']];
            unset($set);
            switch($val['type']) {
                case 'text':
                    $set = $data;
                    break;
                case 'select':
                    if (in_array($data, $val['values'])) {
                        $set = $data;
                    }
                    break;
                case 'bool':
                    if ($data == '1') {
                        $set = '1';
                    } elseif ($data == '0') {
                        $set = '0';
                    }
                    break;
            }
            CONFIG_set($val['name'], $set);
        }
    }
}

HTTP_nocache_headers();

HTML_header();

// Check for extensions
$check = EXTENSIONS_check();

if (count($check) > 0) {
    foreach($check as $name) {
        HTML_message('error', sprintf(_('Can not find needed PHP extension "%s". Please install and enable it.'), $name));
    }
    HTML_footer();
}


// Connect to database
if (!SQL_init()) {
    HTML_die_error(_('Can not connect to MySQL database. Please check your configuration.'));
}

require('./lib/toolbar.php');

if ($cmd == 'update') {
    // Check with possible upgrade
    SQL_check(true);

    // We're done for now
    HTML_message('notice', str_replace('index.php', '<a href="index.php">index.php</a>', _('Tables are in correct state (see above messages about needed changes, if any), you can go back to index.php.')));
} elseif ($cmd == 'save') {
    HTML_message('notice', _('Settings has been updated'));
}

echo '<form class="settings" method="post">';
foreach($settings as $val) {
    $name = $val['name'];
    echo '<div class="opts">';
    echo '<label for="set_' . $name . '">' . $val['text'] . '</label>';
    if ($val['type'] == 'text') {
        echo '<input type="text" name="s_' . $name . '" id="set_' . $name . '" value="' . htmlspecialchars(CONFIG_get($name)) . '" />';
    } else {
        if ($val['type'] == 'select') {
            $opts = $val['values'];
        } else {
            $opts = array('1' => _('Yes'), '0' => _('No'));
        }
        echo '<select name="s_' . $name . '" id="set_' . $name . '" />';
        foreach ($opts as $key => $val) {
            echo '<option value="' . $key . '"';
            if ($key == CONFIG_get($name)) {
                echo ' selected="selected"';
            }
            echo '>' . $val . '</option>';
        }
        echo '</select>';
    }
    echo '</div>';
}
echo '<div class="opts">';
echo '<input type="hidden" name="cmd" value="save" />';
echo '<input type="submit" value="' . _('Save') . '" />';
echo '</div>';
echo '</form>';

// End
HTML_footer();
?>
