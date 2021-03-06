<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is HTML generating stuff for Ukolovnik
// Copyright © 2005 - 2016 Michal Čihař
// Published under GNU GPL version 3 or later

// Grab needed libraries
require_once('./lib/version.php');
require_once('./lib/http.php');
require_once('./lib/config.php');
require_once('./lib/locale.php');

$style_path = './styles/';
$image_path = './images/';

/**
 * Returns list of available styles.
 */
function HTML_list_styles() {
    global $style_path;

    $d = opendir($style_path);
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

    return $styles;
}

/**
 * Displays HTML header.
 */
function HTML_header() {
    global $version, $style_path;

    // Define the charset to be used
    HTTP_type_header('text/html; charset=utf-8');

    // this needs to be echoed otherwise php with short tags complains
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
    <link rel="icon" href="../favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
    <title>Ukolovnik <?php echo $version; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <script type="text/javascript" language="javascript">
    //<![CDATA[
    // show this window in top frame
    if (top != self) {
        window.top.location.href=location;
    }
    //]]>
    </script>
    <link media="all" href="<?php echo $style_path . CONFIG_get('style'); ?>.css" type="text/css" rel="stylesheet" title="<?php echo _('Default style');?>" />
</head>

<body>
<h1>Ukolovnik <?php echo $version; ?></h1>
<?php
}

/**
 * Displays message
 *
 * @param   string $type  type of message (notice/warning/error)
 * @param   string $text  text of message
 * @param   string $title optional title of message
 *
 * @return  nothing
 */
function HTML_message($type, $text, $title = '') {
    echo '<div class="' . $type . '">' . "\n";
    if (!empty($title)) {
        echo '<h1>';
        echo $title;
        echo '</h1>' . "\n";
    }
    echo $text . "\n";
    echo '</div>' . "\n";
}

/**
 * Terminates script and ends HTML
 *
 * @return nothing
 */
function HTML_footer() {
    echo '</body>';
    echo '</html>';
    exit;
}

function HTML_die_error($text) {
    HTML_message('error', $text);
    HTML_footer();
}

function HTML_show_image_link($url, $image, $text) {
    global $image_path;

    echo '<a class="action" href="index.php?' . $url . '">';
    echo '<img src="' . $image_path . CONFIG_get('style') . '/' . $image . '.png" title="' . $text . '" alt="' . $text . '"/>';
    echo '</a> ';
}

function HTML_toolbar($items) {
    echo '<ul class="toolbar">' . "\n";
    foreach($items as $name => $target) {
        echo '<li><a href="' . $target . '">' . $name . "</a></li>\n";
    }
    echo "</ul>\n";
}
