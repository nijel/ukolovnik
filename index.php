<?php
// vim: expandtab sw=4 ts=4 sts=4:

// Whether to show html, used for downloading
$show_html = TRUE;

// Version of this script
$version = '0.1';

/**
 * calls $function vor every element in $array recursively
 *
 * @param   array   $array      array to walk
 * @param   string  $function   function to call for every array element
 */
function arrayWalkRecursive(&$array, $function)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayWalkRecursive($array[$key], $function);
        } else {
            $array[$key] = $function($value);
        }
    }
}

if (get_magic_quotes_gpc()) {
    arrayWalkRecursive($_REQUEST, 'stripslashes');
}

// Grab some parameters
if (empty($_REQUEST['cmd'])) {
    $cmd = 'list';
} else {
    $cmd = $_REQUEST['cmd'];
}

// Required libraries
require('./config.php');

// FIXME: should be configurable
require('./languages/en.php');

// Used later
$now = gmdate('D, d M Y H:i:s') . ' GMT';

// General header for no caching
header('Expires: ' . $now); // rfc2616 - Section 14.21
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

if ($show_html) {

// Define the charset to be used
header('Content-Type: text/html; charset=utf-8');

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
    <link media="all" href="styles/default.css" type="text/css" rel="stylesheet" title="<?php echo $strDefaultStyle;?>" />
</head>

<body>
<h1>Ukolovnik <?php echo $version; ?></h1>
<ul class="toolbar">
<li><a href="index.php"><?php echo $strMain; ?></a></li>
<li><a href="index.php?cmd=add"><?php echo $strAdd; ?></a></li>
<li><a href="index.php?cmd=cat"><?php echo $strCategories; ?></a></li>
<li><a href="index.php?cmd=addcat"><?php echo $strAddCategory; ?></a></li>
<!--
<li><a href="index.php?cmd=export"><?php echo $strExport; ?></a></li>
<li><a href="index.php?cmd=stats"><?php echo $strStats; ?></a></li>
-->
</ul>
<?php
}

/**
 * Displays message
 *
 * @param   string  type of message (notice/warning/error)
 * @param   string  text of message
 * @param   title   optional title of message
 *
 * @return  nothing
 */
function message($type, $text, $title = '') {
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
function footer() {
    echo '</body>';
    echo '</html>';
    exit;
}

function die_error($text) {
    message('error', $text);
    footer();
}

function do_sql($query) {
    global $db, $strSQLFailed;
    $q = mysql_query($query, $db);
    if (!$q) {
        echo mysql_error($db);
        die_error(sprintf($strSQLFailed, htmlspecialchars($query)));
    }
    return $q;
}

function get_check($name) {
    return isset($_REQUEST[$name]) ? 'checked="checked" ' : '';
}

function get_opt($name, $default = '') {
    return empty($_REQUEST[$name]) ? $default : htmlspecialchars($_REQUEST[$name]);
}

function get_select($name, $default, $options, $add_any=FALSE) {
    global $strAny;

    if (isset($_REQUEST[$name]) && strlen($_REQUEST[$name]) > 0) {
        $default = $_REQUEST[$name];
    }
    $ret = '<select id="sel_' . $name . '" name="' . $name . '" onchange="this.form.submit()">';
    if ($add_any) {
        $ret .= '<option value="-1"';
        if ($default == -1) {
            $ret .= ' selected="selected"';
        }
        $ret .= '>' . $strAny . '</option>';
    }
    foreach($options as $key => $val) {
        $ret .= '<option value="' . $key . '"';
        if ($key == $default) {
            $ret .= ' selected="selected"';
        }
        $ret .= '>' . htmlspecialchars($val) . '</option>';
    }
    return $ret . '</select>';
}

function find_links($text) {
    return preg_replace('@((http|ftp|https)://[a-z0-9A-Z.,?&;/=+_-]+)([^.]|$)@', '<a href="\1">\1</a>\3', htmlspecialchars($text));
}

function show_edit_task($name, $cmd, $title, $description, $priority, $category, $id = NULL) {
    global $strTitle, $strDescription, $strPriority, $strCategory;
    global $priorities, $categories;

    echo '<fieldset><legend>' . $name . '</legend><form method="post" action="index.php">';
    if (isset($id)) {
        echo '<input type="hidden" name="id" value="' . $id . 'l" \>';
    }
    echo '<label class="desc">' . $strTitle . '</label>';
    echo '<input type="text" name="title" maxlength="200" value="' . $title . '" />';
    echo '<label class="desc">' . $strDescription . '</label>';
    echo '<textarea name="description" cols="60" rows="5">' . $description . '</textarea>';
    echo '<label class="desc" for="sel_priority">' . $strPriority . '</label>';
    echo get_select('priority', $priority, $priorities);
    echo '<label class="desc" for="sel_category">' . $strCategory . '</label>';
    echo get_select('category', $category, $categories);
    echo '<input type="hidden" name="cmd" value="' . $cmd . '" \>';
    echo '<input type="submit" value="' . $name . '"/></form></fieldset>';
}

function show_edit_category($title, $cmd, $name, $personal, $id = NULL) {
    global $strName, $strPersonal;

    echo '<fieldset><legend>' . $title . '</legend><form method="post" action="index.php">';
    if (isset($id)) {
        echo '<input type="hidden" name="id" value="' . $id . 'l" \>';
    }
    echo '<label class="desc" for="in_name">' . $strName . '</label>';
    echo '<input type="text" id="in_name" name="name" maxlength="200" value="' . $name . '" />';
    echo '<input type="checkbox" id="ch_personal" name="personal" ' . $personal . '/>';
    echo '<label for="ch_personal">' . $strPersonal . '</label>';
    echo '<input type="hidden" name="cmd" value="' . $cmd . '" \>';
    echo '<input type="submit" value="' . $title . '"/></form></fieldset>';
}

function grab_categories() {
    global $categories, $categories_pers, $categories_prof;

    $q = do_sql('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'categories');
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

// Check for MySQL extension
if (!function_exists('mysql_connect')) {
    die_error(sprintf($strExtensionNeeded, 'mysql'));
}

// Check for pcre
if (!function_exists('preg_replace')) {
    die_error(sprintf($strExtensionNeeded, 'pcre'));
}

// Connect to database
$db = @mysql_connect($db_server, $db_user, $db_password);
if ($db === FALSE) {
    die_error($strCanNotConnect);
}

// Connect to database
if (!mysql_select_db($db_database, $db)) {
    die_error($strCanNotSelectDb);
}

// Check tables
$q = do_sql('SHOW TABLES LIKE "' . $GLOBALS['table_prefix'] . 'tasks"');
if (mysql_num_rows($q) == 0) {
    die_error(sprintf($strCanNotFindTable, $GLOBALS['table_prefix'] . 'tasks'));
}
if ($q) mysql_free_result($q);

$q = do_sql('SHOW TABLES LIKE "' . $GLOBALS['table_prefix'] . 'categories"');
if (mysql_num_rows($q) == 0) {
    die_error(sprintf($strCanNotFindTable, $GLOBALS['table_prefix'] . 'categories'));
}
mysql_free_result($q);

// Is there need to handle charset?
$q = do_sql('SELECT VERSION()');
if (mysql_num_rows($q) == 0) {
    die_error(sprintf($strSQLFailed, 'SELECT VERSION()'));
}
$r = mysql_fetch_array($q);
mysql_free_result($q);
$mysql_ver = explode('.', $r[0]);
unset($r);
if (!isset($mysql_ver[0]) || !isset($mysql_ver[1])) {
    die_error(sprintf($strSQLFailed, 'SELECT VERSION()'));
}
// Since MySQL 4 we use utf-8:
if ($mysql_ver[0] >= 5 || ($mysql_ver[0] == 4 && $mysql_ver[1] >= 1)) {
    do_sql('SET NAMES utf8');
    do_sql('SET CHARACTER SET utf8');
}
unset($mysql_ver);


// Grab categories
grab_categories();

// "Grab" priorities
$priorities = array($strPriority0, $strPriority1, $strPriority2);

while (!empty($cmd)) {
    switch($cmd) {
        case 'list':
            if (count($categories) == 0) {
                message('notice', $strNoCategories);
            }

            // Filter
            echo '<fieldset><legend>' . $strFilter . '</legend><form method="get" action="index.php">';
            echo '<label class="desc">' . $strText . '</label>';
            echo '<input type="text" name="text" maxlength="200" value="' . get_opt('text') . '" />';
            echo '<label class="desc" for="sel_priority">' . $strPriority . '</label>';
            echo get_select('priority', -1, $priorities, TRUE);
            echo '<label class="desc" for="sel_category">' . $strCategory . '</label>';
            echo get_select('category', -1, $categories, TRUE);
            echo '<label class="desc" for="sel_personal">' . $strPersonal . '</label>';
            echo get_select('personal', 'all', array('all' => $strAll, 'show' => $strShow, 'hide' => $strHide));
            echo '<label class="desc" for="sel_closed">' . $strFinished . '</label>';
            echo get_select('finished', 'hide', array('all' => $strAll, 'show' => $strShow, 'hide' => $strHide));
            echo '<input type="hidden" name="cmd" value="list" \>';
            echo '<input type="submit" value="' . $strFilter . '"/></form></fieldset>';

            // Apply filter
            $filter = 'WHERE 1';
            if (isset($_REQUEST['category']) && $_REQUEST['category'] != -1) {
                $filter .= ' AND category = ' . (int)$_REQUEST['category'];
            } else {
                if (isset($_REQUEST['personal']) && $_REQUEST['personal'] == 'show') {
                    $filter .= ' AND category IN ( ' . implode(', ', array_keys($categories_pers)) . ' )';
                } elseif (isset($_REQUEST['personal']) && $_REQUEST['personal'] == 'hide') {
                    $filter .= ' AND category IN ( ' . implode(', ', array_keys($categories_prof)) . ' )';
                }
            }
            if (!empty($_REQUEST['text'])) {
                $filter .= ' AND ( title LIKE "%' . addslashes($_REQUEST['text']) . '%" OR description LIKE "%' . addslashes($_REQUEST['text']) . '%")';
            }
            if (isset($_REQUEST['priority']) && $_REQUEST['priority'] != -1) {
                $filter .= ' AND priority = ' . (int)$_REQUEST['priority'];
            }
            if (isset($_REQUEST['finished'])) {
                if ($_REQUEST['finished'] == 'show') {
                    $filter .= ' AND closed <> "00000000000000"';
                } elseif ($_REQUEST['finished'] == 'hide') {
                    $filter .= ' AND (closed IS NULL OR closed = "00000000000000")';
                }
            } else {
                $filter .= ' AND (closed IS NULL OR closed = "00000000000000")';
            }

            // Sorting
            $order = 'priority DESC, created';
            // FIXME: make this parameter
                
            $q = do_sql('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'tasks ' . $filter . ' ORDER BY ' . $order);
            if (mysql_num_rows($q) == 0) {
                message('notice', $strNoEntries);
            } else {
                // Listing
                echo '<table class="listing tasks">';
                echo '<thead><tr>';
                echo '<th>' . $strTitle . '</th>';
                echo '<th>' . $strCategory . '</th>';
                echo '<th>' . $strCreated . '</th>';
                echo '<th>' . $strActions . '</th></tr></thead>';
                echo '<tbody>';
                while ($row = mysql_fetch_assoc($q)) {
                    echo '<tr class="priority' . $row['priority'];
                    if (!is_null($row['closed']) && $row['closed'] != '00000000000000') {
                        echo ' closed';
                    }
                    echo '">';
                    echo '<td class="name"><a href="index.php?cmd=show&amp;id=' . $row['id'] . '">' . htmlspecialchars($row['title']) . '</a></td>';
                    echo '<td class="category"><a href="index.php?category=' . $row['category'] . '">' . htmlspecialchars($categories[$row['category']]) . '</a></td>';
                    echo '<td class="date">' . htmlspecialchars($row['created']) . '</td>';
                    echo '<td class="actions">';
                    echo '<a class="action" href="index.php?cmd=fin&amp;id=' . $row['id'] . '">' . $strFinished . '</a> ';
                    echo '<a class="action" href="index.php?cmd=edit&amp;id=' . $row['id'] . '">' . $strEdit . '</a> ';
                    echo '<a class="action" href="index.php?cmd=del&amp;id=' . $row['id'] . '">' . $strDelete . '</a> ';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
            mysql_free_result($q);
            $cmd = '';
            break;
        case 'show':
            if (!isset($_REQUEST['id'])) {
                die_error($strParameterInvalid);
            }
            $q = do_sql('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
            if (mysql_num_rows($q) != 1) {
                message('notice', $strNoEntries);
            } else {
                // Listing
                $row = mysql_fetch_assoc($q);
                echo '<fieldset class="priority' . $row['priority'] . '"><legend>' . htmlspecialchars($row['title'] . '(' . $categories[$row['category']] . ')' ) . '</legend>';
                echo '<p>' . nl2br(find_links($row['description'])) . '</p>';
                echo '<p>' . $strCreated . ': ' . htmlspecialchars($row['created']) . '</p>';
                if (!is_null($row['updated']) && $row['updated'] != '00000000000000') {
                    echo '<p>' . $strUpdated . ': ' . htmlspecialchars($row['updated']) . '</p>';
                }
                if (!is_null($row['closed']) && $row['closed'] != '00000000000000') {
                    echo '<p>' . $strClosed . ': ' . htmlspecialchars($row['closed']) . '</p>';
                }
                echo '<p class="actions">';
                echo '<a class="action" href="index.php?cmd=fin&amp;id=' . $row['id'] . '">' . $strFinished . '</a> ';
                echo '<a class="action" href="index.php?cmd=edit&amp;id=' . $row['id'] . '">' . $strEdit . '</a> ';
                echo '<a class="action" href="index.php?cmd=del&amp;id=' . $row['id'] . '">' . $strDelete . '</a> ';
                echo '</p>';
                echo '</fieldset>';
            }
            mysql_free_result($q);
            $cmd = '';
            break;
        case 'fin':
            if (!isset($_REQUEST['id'])) {
                die_error($strParameterInvalid);
            }
            $q = do_sql('SELECT title FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
            if (mysql_num_rows($q) != 1) {
                message('notice', $strNoEntries);
            } else {
                $row = mysql_fetch_assoc($q);
                do_sql('UPDATE ' . $GLOBALS['table_prefix'] . 'tasks SET closed=NOW() WHERE id=' . (int)$_REQUEST['id']);
                message('notice', sprintf($strTaskFinished, htmlspecialchars($row['title'])));
            }
            mysql_free_result($q);
            $cmd = 'list';
            break;
        case 'del':
            if (!isset($_REQUEST['id'])) {
                die_error($strParameterInvalid);
            }
            $q = do_sql('SELECT title FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
            if (mysql_num_rows($q) != 1) {
                message('notice', $strNoEntries);
            } else {
                $row = mysql_fetch_assoc($q);
                do_sql('DELETE FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
                message('notice', sprintf($strTaskDeleted, htmlspecialchars($row['title'])));
            }
            mysql_free_result($q);
            $cmd = 'list';
            break;
        case 'edit':
            if (!isset($_REQUEST['id'])) {
                message('error', $strInvalidId);
                $cmd = '';
                break;
            }
            $id = (int)$_REQUEST['id'];
            $q = do_sql('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . $id);
            if (mysql_num_rows($q) != 1) {
                message('error', $strInvalidId);
                $cmd = '';
                break;
            } else {
                $row = mysql_fetch_assoc($q);
                mysql_free_result($q);
                show_edit_task($strEdit, 'edit_real', htmlspecialchars($row['title']), htmlspecialchars($row['description']), $row['priority'], $row['category'], $id);
                $cmd = '';
            }
            break;
        case 'edit_real';
        case 'add_real';
            $error = FALSE;
            if ($cmd == 'edit_real') {
                if (!isset($_REQUEST['id'])) {
                    message('error', $strInvalidId);
                    $error = TRUE;
                } else { 
                    $id = (int)$_REQUEST['id'];
                    if ($id <= 0) {
                        message('error', $strInvalidId);
                        $error = TRUE;
                    }
                    $q = do_sql('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . $id);
                    if (mysql_num_rows($q) != 1) {
                        message('error', $strInvalidId);
                        $error = TRUE;
                    }
                }
            }
            if (empty($_REQUEST['title'])) {
                message('error', $strTitleNotEmpty);
                $error = TRUE;
            }
            if (empty($_REQUEST['category'])) {
                message('error', $strCategoryInvalid);
                $error = TRUE;
            } else { 
                $category = (int)$_REQUEST['category'];
                if (!isset($categories[$category])) {
                    message('error', $strCategoryInvalid);
                    $error = TRUE;
                }
            }
            if (!isset($_REQUEST['priority'])) {
                message('error', $strPriorityInvalid);
                $error = TRUE;
            } else { 
                $priority = (int)$_REQUEST['priority'];
                if ($priority < 0 || $priority > 2) {
                    message('error', $strPriorityInvalid);
                    $error = TRUE;
                }
            }
            if (empty($_REQUEST['description'])) {
                $_REQUEST['description'] = '';
            }
            if (!$error) {
                $set_sql = 'SET '
                    . 'title="' . addslashes($_REQUEST['title']) . '"'
                    . ', description="' . addslashes($_REQUEST['description']) . '"'
                    . ', category= ' . $category
                    . ', priority= ' . $priority;
                if ($cmd == 'add_real') {
                    do_sql('INSERT INTO ' . $GLOBALS['table_prefix'] . 'tasks ' . $set_sql);
                    message('notice', sprintf($strTaskAdded, htmlspecialchars($_REQUEST['title'])));
                } else {
                    do_sql('UPDATE ' . $GLOBALS['table_prefix'] . 'tasks ' . $set_sql . ', updated=NOW() WHERE id=' . $id);
                    message('notice', sprintf($strTaskChanged, htmlspecialchars($_REQUEST['title'])));
                }
                // To avoid filtering
                unset($_REQUEST['priority'], $_REQUEST['category']);
                $cmd = 'list';
                break;
            }
        case 'add':
            if ($cmd == 'edit_real') {
                show_edit_task($strEdit, 'edit_real', get_opt('title'), get_opt('description'), get_opt('priority', 1), get_opt('category', -1), $id);
            } else { 
                show_edit_task($strAdd, 'add_real', get_opt('title'), get_opt('description'), get_opt('priority', 1), get_opt('category', -1));
            }
            $cmd = '';
            break;
        case 'editcat':
            if (!isset($_REQUEST['id'])) {
                message('error', $strInvalidId);
                $cmd = '';
                break;
            }
            $id = (int)$_REQUEST['id'];
            if (!isset($categories[$id])) {
                message('error', $strInvalidId);
                $cmd = '';
                break;
            } else {
                show_edit_category($strEditCategory, 'editcat_real', htmlspecialchars($categories[$id]), isset($categories_pers[$id]) ? ' checked="checked"' : '', $id);
                $cmd = '';
            }
            break;
        case 'editcat_real':
        case 'addcat_real':
            $error = FALSE;
            if ($cmd == 'editcat_real') {
                if (!isset($_REQUEST['id'])) {
                    message('error', $strInvalidId);
                    $error = TRUE;
                } else { 
                    $id = (int)$_REQUEST['id'];
                    if ($id <= 0) {
                        message('error', $strInvalidId);
                        $error = TRUE;
                    }
                    if (!isset($categories[$id])) {
                        message('error', $strInvalidId);
                        $error = TRUE;
                    }
                }
            }
            if (empty($_REQUEST['name'])) {
                message('error', $strNameNotEmpty);
                $error = TRUE;
            }
            if (isset($_REQUEST['personal'])) {
                $personal = '1';
            } else {
                $personal = '0';
            }
            if (!$error) {
                $set_sql = 'SET name="' . addslashes($_REQUEST['name']) . '", personal=' . $personal;
                if ($cmd == 'addcat_real') {
                    do_sql('INSERT INTO ' . $GLOBALS['table_prefix'] . 'categories ' . $set_sql);
                    message('notice', sprintf($strCategoryAdded, htmlspecialchars($_REQUEST['name'])));
                } else {
                    do_sql('UPDATE ' . $GLOBALS['table_prefix'] . 'categories ' . $set_sql . ' WHERE id=' . $id);
                    message('notice', sprintf($strCategoryChanged, htmlspecialchars($_REQUEST['name'])));
                }
                // To avoid filtering
                unset($_REQUEST['personal']);
                // Reread categories
                grab_categories();
                $cmd = 'cat';
                break;
            }
        case 'addcat':
            if ($cmd == 'editcat_real') {
                show_edit_category($strEditCategory, 'editcat_real', get_opt('name'), get_check('personal'), $id);
            } else {
                show_edit_category($strAddCategory, 'addcat_real', get_opt('name'), get_check('personal'));
            }
            $cmd = '';
            break;
        case 'cat':
            // Which categories to display?
            if (isset($_REQUEST['personal']) && $_REQUEST['personal'] == 'show') {
                $cats = $categories_pers;
            } elseif (isset($_REQUEST['personal']) && $_REQUEST['personal'] == 'hide') {
                $cats = $categories_prof;
            } else {
                $cats = $categories;
            }

            if (count($cats) == 0) {
                message('notice', $strNoCategories);
            } else {
                // Filter
                echo '<fieldset><legend>' . $strFilter . '</legend><form method="get" action="index.php">';
                echo '<label class="desc" for="sel_personal">' . $strPersonal . '</label>';
                echo get_select('personal', 'all', array('all' => $strAll, 'show' => $strShow, 'hide' => $strHide));
                echo '<input type="hidden" name="cmd" value="cat" \>';
                echo '<input type="submit" value="' . $strFilter . '"/></form></fieldset>';

                // Listing
                echo '<table class="listing">';
                echo '<thead><tr><th>' . $strName . '</th><th>' . $strPersonal . '</th><th>' . $strActions . '</th></tr></thead>';
                echo '<tbody>';
                foreach($cats as $id => $name) {
                    echo '<tr><td class="name"><a href="index.php?category=' . $id . '">' . htmlspecialchars($name) . '</a></td>';
                    echo '<td class="name">' . ( isset($categories_pers[$id]) ? $strYes : $strNo ) . '</td>';
                    echo '<td class="actions">';
                    echo '<a class="action" href="index.php?cmd=editcat&amp;id=' . $id . '">' . $strEdit . '</a>';
                    echo '<a class="action" href="index.php?cmd=delcat&amp;id=' . $id . '">' . $strDelete . '</a> ';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
            $cmd = '';
            break;
        default:
            message('error', $strUnknownCommand);
            footer();
    }
}
?>
