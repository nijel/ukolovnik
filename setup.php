<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is setup file for Ukolovnik
// Copyright © 2005 - 2013 Michal Čihař
// Published under GNU GPL version 3 or later

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

// Strip possible slashes in REQUEST
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
$styles = HTML_list_styles();

function N_($text) {
    return $text;
}

$settings = array(
    /* l10n: please keep also English text "Language" in translated text */
    array('name' => 'language', 'text' => N_('Language'), 'type' => 'select', 'values' => $langs),
    array('name' => 'style', 'text' => N_('Style'), 'type' => 'select', 'values' => $styles),
    array('name' => 'add_stay', 'text' => N_('Stay on add page after adding new entry'), 'type' => 'bool'),
    array('name' => 'add_list', 'text' => N_('Show entries list on add page'), 'type' => 'bool'),
    array('name' => 'main_style', 'text' => N_('Show category name in main page output'), 'type' => 'bool'),
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
    $failed_lang = LOCALE_init();
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
    $message = $val['text'];
    echo '<div class="opts">' . "\n";
    echo '<label for="set_' . $name . '">' . gettext($message) . '</label>' . "\n";
    if ($val['type'] == 'text') {
        echo '<input type="text" name="s_' . $name . '" id="set_' . $name . '" value="' . htmlspecialchars(CONFIG_get($name)) . '" />' . "\n";
    } else {
        if ($val['type'] == 'select') {
            $opts = $val['values'];
        } else {
            $opts = array('1' => _('Yes'), '0' => _('No'));
        }
        echo '<select name="s_' . $name . '" id="set_' . $name . '" />' . "\n";
        foreach ($opts as $key => $val) {
            echo '<option value="' . $key . '"';
            if ($key == CONFIG_get($name)) {
                echo ' selected="selected"';
            }
            echo '>' . $val . '</option>' . "\n";
        }
        echo '</select>' . "\n";
    }
    echo '</div>' . "\n";
}
echo '<div class="opts">' . "\n";
echo '<input type="hidden" name="cmd" value="save" />' . "\n";
echo '<input type="submit" value="' . _('Save') . '" />' . "\n";
echo '</div>' . "\n";
echo '</form>' . "\n";

// End
HTML_footer();
?>
