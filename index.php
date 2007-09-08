<?php
// vim: expandtab sw=4 ts=4 sts=4:

// This is main file for Ukolovnik
// Copyright (c) 2005 - 2007 Michal Čihař
// Published under GNU GPL version 2

// Grab needed libaries
require_once('./lib/version.php');
require_once('./lib/sql.php');
require_once('./lib/http.php');
require_once('./lib/html.php');
require_once('./lib/config.php');
require_once('./lib/string.php');
require_once('./lib/category.php');
require_once('./lib/priority.php');
require_once('./lib/extensions.php');

// Whether to show html, used for downloading
$show_html = TRUE;

// Grab some parameters
if (empty($_REQUEST['cmd'])) {
    $cmd = 'list';
} else {
    $cmd = $_REQUEST['cmd'];
}

// For export we don't want html
if (substr($cmd, 0, 7) == 'export_') {
    $show_html = false;
}

// Include correct language file
$failed_lang = LOCALE_init();

HTTP_nocache_headers();

if ($show_html) {
    HTML_header();
}

function get_check($name) {
    return isset($_REQUEST[$name]) ? 'checked="checked" ' : '';
}

function get_opt($name, $default = '') {
    return empty($_REQUEST[$name]) ? $default : htmlspecialchars($_REQUEST[$name]);
}

function get_select($name, $default, $options, $add_any=FALSE, $autosubmit=FALSE) {
    if (isset($_REQUEST[$name]) && strlen($_REQUEST[$name]) > 0) {
        $default = $_REQUEST[$name];
    }
    $ret = '<select id="sel_' . $name . '" name="' . $name . '"';
    if ($autosubmit) {
        $ret .= ' onchange="this.form.submit()"';
    }
    $ret .= ">\n";
    if ($add_any) {
        $ret .= '<option value="-1"';
        if ($default == -1) {
            $ret .= ' selected="selected"';
        }
        $ret .= '>' . LOCALE_get('Any') . '</option>';
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

function show_edit_task($name, $cmd, $title, $description, $priority, $category, $update_count, $id = NULL) {
    global $priorities, $categories;

    echo '<fieldset><legend>' . $name . '</legend><form method="post" action="index.php">';
    if (isset($id)) {
        echo '<input type="hidden" name="id" value="' . $id . '" \>';
    }
    echo '<label class="desc" for="t_title">' . LOCALE_get('Title') . '</label>';
    echo '<input type="text" name="title" id="t_title" maxlength="200" value="' . $title . '" />';
    echo '<label class="desc" for="t_description">' . LOCALE_get('Description') . '</label>';
    echo '<textarea name="description" id="t_description" cols="60" rows="5">' . $description . '</textarea>';
    echo '<label class="desc" for="sel_priority">' . LOCALE_get('Priority') . '</label>';
    echo get_select('priority', $priority, $priorities);
    echo '<label class="desc" for="sel_category">' . LOCALE_get('Category') . '</label>';
    echo get_select('category', $category, $categories);
    echo '<input type="hidden" name="cmd" value="' . $cmd . '" \>';
	echo '<input type="hidden" name="update_count" value="' . $update_count . '" \>';
    echo '<input type="submit" value="' . $name . '"/></form></fieldset>';
}

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

// Check for needed tables and databases
$check = SQL_check();

if (in_array('db', $check)) {
    HTML_message('error', str_replace('setup.php', '<a href="setup.php?cmd=update">setup.php</a>', LOCALE_get('CanNotSelectDb')));
}

foreach ($required_tables as $tbl) {
    if (in_array($tbl, $check)) {
        HTML_message('error', str_replace('setup.php', '<a href="setup.php?cmd=update">setup.php</a>', sprintf(LOCALE_get('CanNotFindTable'), SQL_name($tbl))));
    }
}

if (isset($check['upgrade'], $check)) {
    foreach ($check['upgrade'] as $tbl) {
        HTML_message('error', str_replace('setup.php', '<a href="setup.php?cmd=update">setup.php</a>', sprintf(LOCALE_get('TableNeedsUpdate'), SQL_name($tbl))));
    }
}

if (count($check) > 0) {
    HTML_footer();
}

// Could we locate language file?
if ($failed_lang) {
    HTML_message('warning', sprintf(LOCALE_get('InvalidLanguage'), $language));
}

if ($show_html) {
    require('./lib/toolbar.php');
}

// Grab categories and priorities
CATEGORY_grab();
PRIORITY_grab();

while (!empty($cmd)) {
    switch($cmd) {
        case 'list':
            if (count($categories) == 0) {
                HTML_message('notice', LOCALE_get('NoCategories'));
            }

            // Filter
            echo '<fieldset class="filter"><legend>' . LOCALE_get('Filter') . '</legend><form method="get" action="index.php">';
            echo '<label class="desc" for="t_text">' . LOCALE_get('Text') . '</label>';
            echo '<input type="text" name="text" id="t_text" maxlength="200" value="' . get_opt('text') . '" />';
            echo '<label class="desc" for="sel_priority">' . LOCALE_get('Priority') . '</label>';
            echo get_select('priority', -1, $priorities, TRUE, TRUE);
            echo '<label class="desc" for="sel_category">' . LOCALE_get('Category') . '</label>';
            echo get_select('category', -1, $categories, TRUE, TRUE);
            echo '<label class="desc" for="sel_personal">' . LOCALE_get('Personal') . '</label>';
            echo get_select('personal', 'all', array('all' => LOCALE_get('All'), 'show' => LOCALE_get('Show'), 'hide' => LOCALE_get('Hide')), FALSE, TRUE);
            echo '<label class="desc" for="sel_finished">' . LOCALE_get('Finished') . '</label>';
            echo get_select('finished', 'hide', array('all' => LOCALE_get('All'), 'show' => LOCALE_get('Show'), 'hide' => LOCALE_get('Hide')), FALSE, TRUE);
            echo '<input type="hidden" name="cmd" value="list" \>';
            echo '<input type="submit" value="' . LOCALE_get('Filter') . '"/></form></fieldset>';

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
            $order = 'priority DESC, created ASC';
            // FIXME: make this parameter

            $q = SQL_do('SELECT id,category,UNIX_TIMESTAMP(created) AS created,priority,title,UNIX_TIMESTAMP(closed) AS closed FROM ' . $GLOBALS['table_prefix'] . 'tasks ' . $filter . ' ORDER BY ' . $order);
            if (mysql_num_rows($q) == 0) {
                HTML_message('notice', LOCALE_get('NoEntries'));
            } else {
                // Listing
                echo '<table class="listing tasks">';
                echo '<thead><tr>';
                echo '<th>' . LOCALE_get('Title') . '</th>';
                echo '<th>' . LOCALE_get('Category') . '</th>';
                echo '<th>' . LOCALE_get('Created') . '</th>';
                echo '<th>' . LOCALE_get('Actions') . '</th></tr></thead>';
                echo '<tbody>';
                while ($row = mysql_fetch_assoc($q)) {
                    echo '<tr class="priority' . $row['priority'];
                    if (!is_null($row['closed']) && $row['closed'] != 0) {
                        echo ' closed';
                    }
                    echo '">';
                    echo '<td class="name"><a href="index.php?cmd=show&amp;id=' . $row['id'] . '">' . htmlspecialchars($row['title']) . '</a></td>';
                    echo '<td class="category"><a href="index.php?category=' . $row['category'] . '">' . htmlspecialchars($categories[$row['category']]) . '</a></td>';
                    echo '<td class="date">' . STRING_format_date($row['created']) . '</td>';
                    echo '<td class="actions">';
                    if (!is_null($row['closed']) && $row['closed'] != 0) {
                        HTML_show_image_link('cmd=reopen&amp;id=' . $row['id'], 'reopen', LOCALE_get('Reopen'));
                    } else {
                        HTML_show_image_link('cmd=fin&amp;id=' . $row['id'], 'finished', LOCALE_get('Finish'));
                    }
                    HTML_show_image_link('cmd=edit&amp;id=' . $row['id'], 'edit', LOCALE_get('Edit'));
                    HTML_show_image_link('cmd=del&amp;id=' . $row['id'], 'delete', LOCALE_get('Delete'));
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
                HTML_die_error(LOCALE_get('ParameterInvalid'));
            }
            $q = SQL_do('SELECT id,category,UNIX_TIMESTAMP(created) AS created,priority,title,UNIX_TIMESTAMP(closed) AS closed,UNIX_TIMESTAMP(updated) AS updated,description FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
            if (mysql_num_rows($q) != 1) {
                HTML_message('notice', LOCALE_get('NoEntries'));
            } else {
                // Listing
                $row = mysql_fetch_assoc($q);
                echo '<fieldset class="priority' . $row['priority'] . '"><legend>' . htmlspecialchars($row['title'] . '(' . $categories[$row['category']] . ')' ) . '</legend>';
                echo '<p>' . nl2br(STRING_find_links($row['description'])) . '</p>';
                echo '<p>' . LOCALE_get('Created') . ': ' . STRING_format_date($row['created']) . '</p>';
                if (!is_null($row['updated']) && $row['updated'] != 0) {
                    echo '<p>' . LOCALE_get('Updated') . ': ' . STRING_format_date($row['updated']) . '</p>';
                }
                if (!is_null($row['closed']) && $row['closed'] != 0) {
                    echo '<p>' . LOCALE_get('Closed') . ': ' . STRING_format_date($row['closed']) . '</p>';
                }
                echo '<p class="actions">';

                if (!is_null($row['closed']) && $row['closed'] != 0) {
                    HTML_show_image_link('cmd=reopen&amp;id=' . $row['id'], 'reopen', LOCALE_get('Reopen'));
                } else {
                    HTML_show_image_link('cmd=fin&amp;id=' . $row['id'], 'finished', LOCALE_get('Finish'));
                }
                HTML_show_image_link('cmd=edit&amp;id=' . $row['id'], 'edit', LOCALE_get('Edit'));
                HTML_show_image_link('cmd=del&amp;id=' . $row['id'], 'delete', LOCALE_get('Delete'));
                echo '</p>';
                echo '</fieldset>';
            }
            mysql_free_result($q);
            $cmd = '';
            break;
        case 'reopen':
            if (!isset($_REQUEST['id'])) {
                HTML_die_error(LOCALE_get('ParameterInvalid'));
            }
            $q = SQL_do('SELECT title FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
            if (mysql_num_rows($q) != 1) {
                HTML_message('notice', LOCALE_get('NoEntries'));
            } else {
                $row = mysql_fetch_assoc($q);
                SQL_do('UPDATE ' . $GLOBALS['table_prefix'] . 'tasks SET closed=NULL, created=created WHERE id=' . (int)$_REQUEST['id']);
                HTML_message('notice', sprintf(LOCALE_get('TaskReopened'), htmlspecialchars($row['title'])));
            }
            mysql_free_result($q);
            $cmd = 'list';
            break;
        case 'fin':
            if (!isset($_REQUEST['id'])) {
                HTML_die_error(LOCALE_get('ParameterInvalid'));
            }
            $q = SQL_do('SELECT title FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
            if (mysql_num_rows($q) != 1) {
                HTML_message('notice', LOCALE_get('NoEntries'));
            } else {
                $row = mysql_fetch_assoc($q);
                SQL_do('UPDATE ' . $GLOBALS['table_prefix'] . 'tasks SET closed=NOW(), created=created WHERE id=' . (int)$_REQUEST['id']);
                HTML_message('notice', sprintf(LOCALE_get('TaskFinished'), htmlspecialchars($row['title'])));
            }
            mysql_free_result($q);
            $cmd = 'list';
            break;
        case 'del':
            if (!isset($_REQUEST['id'])) {
                HTML_die_error(LOCALE_get('ParameterInvalid'));
            }
            $q = SQL_do('SELECT title FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
            if (mysql_num_rows($q) != 1) {
                HTML_message('notice', LOCALE_get('NoEntries'));
            } else {
                $row = mysql_fetch_assoc($q);
                SQL_do('DELETE FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . (int)$_REQUEST['id']);
                HTML_message('notice', sprintf(LOCALE_get('TaskDeleted'), htmlspecialchars($row['title'])));
            }
            mysql_free_result($q);
            $cmd = 'list';
            break;
        case 'edit':
            if (!isset($_REQUEST['id'])) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            }
            $id = (int)$_REQUEST['id'];
            $q = SQL_do('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . $id);
            if (mysql_num_rows($q) != 1) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            } else {
                $row = mysql_fetch_assoc($q);
                mysql_free_result($q);
                show_edit_task(LOCALE_get('Edit'), 'edit_real', htmlspecialchars($row['title']), htmlspecialchars($row['description']), $row['priority'], $row['category'], $row['update_count'], $id);
                $cmd = '';
            }
            break;
        case 'edit_real';
        case 'add_real';
            $error = FALSE;
            if ($cmd == 'edit_real') {
                if (!isset($_REQUEST['id'])) {
                    HTML_message('error', LOCALE_get('InvalidId'));
                    $error = TRUE;
                } else {
                    $id = (int)$_REQUEST['id'];
                    if ($id <= 0) {
                        HTML_message('error', LOCALE_get('InvalidId'));
                        $error = TRUE;
                    }
                    $q = SQL_do('SELECT * FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE id=' . $id);
                    if (mysql_num_rows($q) != 1) {
                        HTML_message('error', LOCALE_get('InvalidId'));
                        $error = TRUE;
                    }
                }
            }
            if (empty($_REQUEST['title'])) {
                HTML_message('error', LOCALE_get('TitleNotEmpty'));
                $error = TRUE;
            }
            if (empty($_REQUEST['category'])) {
                HTML_message('error', LOCALE_get('CategoryInvalid'));
                $error = TRUE;
            } else {
                $category = (int)$_REQUEST['category'];
                if (!isset($categories[$category])) {
                    HTML_message('error', LOCALE_get('CategoryInvalid'));
                    $error = TRUE;
                }
            }
            if (!isset($_REQUEST['priority'])) {
                HTML_message('error', LOCALE_get('PriorityInvalid'));
                $error = TRUE;
            } else {
                $priority = (int)$_REQUEST['priority'];
                if ($priority < 0 || $priority > 2) {
                    HTML_message('error', LOCALE_get('PriorityInvalid'));
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
                    SQL_do('INSERT INTO ' . $GLOBALS['table_prefix'] . 'tasks ' . $set_sql);
                    HTML_message('notice', sprintf(LOCALE_get('TaskAdded'), htmlspecialchars($_REQUEST['title'])));
                } else {
				    $cnt = (int) $_REQUEST['update_count'];
                    SQL_do('UPDATE ' . $GLOBALS['table_prefix'] . 'tasks ' . $set_sql . ', updated=NOW(), update_count='. ($cnt+1) . ' WHERE id=' . $id . ' AND update_count='.$cnt);

					$r=mysql_affected_rows();
					if (!$r) {
						HTML_message('error', LOCALE_get('ConcurrecyError'));
					}
					else {
                    HTML_message('notice', sprintf(LOCALE_get('TaskChanged'), htmlspecialchars($_REQUEST['title'])));
                }
                }
                // To avoid filtering
                unset($_REQUEST['priority'], $_REQUEST['category']);
                // Add next item after adding one
                if (!CONFIG_get('add_stay') || $cmd == 'edit_real') {
                    $cmd = 'list';
                    break;
                }
            }
        case 'add':
            if ($cmd == 'edit_real') {
                show_edit_task(LOCALE_get('Edit'), 'edit_real', get_opt('title'), get_opt('description'), get_opt('priority', 1), get_opt('category', -1), get_opt('update_count',0), $id);
            } else {
                show_edit_task(LOCALE_get('Add'), 'add_real', get_opt('title'), get_opt('description'), get_opt('priority', 1), get_opt('category', -1), get_opt('update_count',0));
            }
            // Show listing on add page?
            if (CONFIG_get('add_list')) {
                $cmd = 'list';
            } else {
                $cmd = '';
            }
            break;
        case 'editcat':
            if (!isset($_REQUEST['id'])) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            }
            $id = (int)$_REQUEST['id'];
            if (!isset($categories[$id])) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            } else {
                CATEGORY_show_edit(LOCALE_get('EditCategory'), 'editcat_real', htmlspecialchars($categories[$id]), isset($categories_pers[$id]) ? ' checked="checked"' : '', $id);
                $cmd = '';
            }
            break;
        case 'editcat_real':
        case 'addcat_real':
            $error = FALSE;
            if ($cmd == 'editcat_real') {
                if (!isset($_REQUEST['id'])) {
                    HTML_message('error', LOCALE_get('InvalidId'));
                    $error = TRUE;
                } else {
                    $id = (int)$_REQUEST['id'];
                    if ($id <= 0) {
                        HTML_message('error', LOCALE_get('InvalidId'));
                        $error = TRUE;
                    }
                    if (!isset($categories[$id])) {
                        HTML_message('error', LOCALE_get('InvalidId'));
                        $error = TRUE;
                    }
                }
            }
            if (empty($_REQUEST['name'])) {
                HTML_message('error', LOCALE_get('NameNotEmpty'));
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
                    SQL_do('INSERT INTO ' . $GLOBALS['table_prefix'] . 'categories ' . $set_sql);
                    HTML_message('notice', sprintf(LOCALE_get('CategoryAdded'), htmlspecialchars($_REQUEST['name'])));
                } else {
                    SQL_do('UPDATE ' . $GLOBALS['table_prefix'] . 'categories ' . $set_sql . ' WHERE id=' . $id);
                    HTML_message('notice', sprintf(LOCALE_get('CategoryChanged'), htmlspecialchars($_REQUEST['name'])));
                }
                // To avoid filtering
                unset($_REQUEST['personal']);
                // Reread categories
                CATEGORY_grab();
                $cmd = 'cat';
                break;
            }
        case 'addcat':
            if ($cmd == 'editcat_real') {
                CATEGORY_show_edit(LOCALE_get('EditCategory'), 'editcat_real', get_opt('name'), get_check('personal'), $id);
            } else {
                CATEGORY_show_edit(LOCALE_get('AddCategory'), 'addcat_real', get_opt('name'), get_check('personal'));
            }
            $cmd = '';
            break;
        case 'delcat_real':
            if (!isset($_REQUEST['id'])) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            }
            $id = (int)$_REQUEST['id'];
            if (!isset($categories[$id])) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            }

            $q = SQL_do('SELECT COUNT(id) AS cnt FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE category = ' . $id);
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                if ($row['cnt'] > 0) {
                    if (!isset($_REQUEST['tasks']) || ($_REQUEST['tasks'] != 'delete' && $_REQUEST['tasks'] != 'move')) {
                        HTML_message('error', LOCALE_get('ParameterInvalid'));
                        $cmd = '';
                        break;
                    }
                    if ($_REQUEST['tasks'] == 'delete') {
                        SQL_do('DELETE FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE category = ' . $id);
                        SQL_do('DELETE FROM ' . $GLOBALS['table_prefix'] . 'categories WHERE id = ' . $id . ' LIMIT 1');
                        HTML_message('notice', sprintf(LOCALE_get('CategoryDeleted'), htmlspecialchars($categories[$id])));
                    } else {

                        if (!isset($_REQUEST['newcat'])) {
                            HTML_message('error', LOCALE_get('InvalidId'));
                            $cmd = '';
                            break;
                        }
                        $newcat = (int)$_REQUEST['newcat'];
                        if (!isset($categories[$newcat])) {
                            HTML_message('error', LOCALE_get('InvalidId'));
                            $cmd = '';
                            break;
                        }

                        SQL_do('UPDATE ' . $GLOBALS['table_prefix'] . 'tasks SET category = ' . $newcat . ' WHERE category = ' . $id);
                        SQL_do('DELETE FROM ' . $GLOBALS['table_prefix'] . 'categories WHERE id = ' . $id . ' LIMIT 1');
                        HTML_message('notice', sprintf(LOCALE_get('CategoryDeleted'), htmlspecialchars($categories[$id])));
                    }
                } else {
                    SQL_do('DELETE FROM ' . $GLOBALS['table_prefix'] . 'categories WHERE id = ' . $id . ' LIMIT 1');
                    HTML_message('notice', sprintf(LOCALE_get('CategoryDeleted'), htmlspecialchars($categories[$id])));
                }
            } else {
                SQL_do('DELETE FROM ' . $GLOBALS['table_prefix'] . 'categories WHERE id = ' . $id . ' LIMIT 1');
                HTML_message('notice', sprintf(LOCALE_get('CategoryDeleted'), htmlspecialchars($categories[$id])));
            }

            // Reread categories
            CATEGORY_grab();
            $cmd = 'cat';
            break;
        case 'delcat':
            if (!isset($_REQUEST['id'])) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            }
            $id = (int)$_REQUEST['id'];
            if (!isset($categories[$id])) {
                HTML_message('error', LOCALE_get('InvalidId'));
                $cmd = '';
                break;
            }

            echo '<fieldset><legend>' . htmlspecialchars(sprintf(LOCALE_get('DeleteCategory'), $categories[$id])) . '</legend><form method="post" action="index.php">';
            echo '<input type="hidden" name="id" value="' . $id . '" \>';
            $q = SQL_do('SELECT COUNT(id) AS cnt FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE category = ' . $id);
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                if ($row['cnt'] > 0) {
                    echo '<p>' . sprintf(LOCALE_get('TasksInCategory'), $row['cnt']) . '</p>';
                    echo '<p>' . LOCALE_get('SelectDeleteTask') . '</p>';
                    echo '<input name="tasks" value="delete" type="radio" id="r_delete" />';
                    echo '<label for="r_delete">' . LOCALE_get('Delete') . '</label>';
                    echo '<input name="tasks" value="move" type="radio" id="r_move" checked="checked" />';
                    echo '<label for="r_move">' . LOCALE_get('MoveTo') . '</label>';
                    echo '<label class="desc" for="sel_category">' . LOCALE_get('TargetCategory') . '</label>';
                    $cats = $categories;
                    unset($cats[$id]);
                    echo get_select('newcat', -1, $cats);
                    unset($cats);
                } else {
                    echo '<p>' . LOCALE_get('NoTaskCategory') . '</p>';
                }
            } else {
                echo '<p>' . LOCALE_get('NoTaskCategory') . '</p>';
            }
            echo '<input type="hidden" name="cmd" value="delcat_real" \>';
            echo '<input type="submit" value="' . LOCALE_get('Delete') . '"/></form></fieldset>';

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
                HTML_message('notice', LOCALE_get('NoCategories'));
            } else {
                // Filter
                echo '<fieldset class="filter"><legend>' . LOCALE_get('Filter') . '</legend><form method="get" action="index.php">';
                echo '<label class="desc" for="sel_personal">' . LOCALE_get('Personal') . '</label>';
                echo get_select('personal', 'all', array('all' => LOCALE_get('All'), 'show' => LOCALE_get('Show'), 'hide' => LOCALE_get('Hide')), FALSE, TRUE);
                echo '<input type="hidden" name="cmd" value="cat" \>';
                echo '<input type="submit" value="' . LOCALE_get('Filter') . '"/></form></fieldset>';

                // Listing
                echo '<table class="listing">';
                echo '<thead><tr><th>' . LOCALE_get('Name') . '</th><th>' . LOCALE_get('Personal') . '</th><th>' . LOCALE_get('Actions') . '</th></tr></thead>';
                echo '<tbody>';
                foreach($cats as $id => $name) {
                    echo '<tr class="nopriority"><td class="name"><a href="index.php?category=' . $id . '">' . htmlspecialchars($name) . '</a></td>';
                    echo '<td class="name">' . ( isset($categories_pers[$id]) ? LOCALE_get('Yes') : LOCALE_get('No') ) . '</td>';
                    echo '<td class="actions">';
                    HTML_show_image_link('cmd=editcat&amp;id=' . $id, 'edit', LOCALE_get('Edit'));
                    HTML_show_image_link('cmd=delcat&amp;id=' . $id, 'delete', LOCALE_get('Delete'));
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
            $cmd = '';
            break;
        case 'stats':
            echo '<table class="listing">';
            echo '<thead><tr><th>' . LOCALE_get('Name') . '</th><th>' . LOCALE_get('Item') . '</th></tr></thead>';
            echo '<tbody>';

            $q = SQL_do('SELECT COUNT(id) as cnt FROM ' . $GLOBALS['table_prefix'] . 'tasks');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('TotalTaskCount') . '</td>';
                echo '<td class="value number">' . $row['cnt'] . '</td></tr>';
            }
            mysql_free_result($q);

            $q = SQL_do('SELECT COUNT(id) as cnt FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE (closed IS NULL or closed = 0)');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OpenedTaskCount') . '</td>';
                echo '<td class="value number">' . $row['cnt'] . '</td></tr>';
            }
            mysql_free_result($q);

            $q = SQL_do('SELECT COUNT(id) as cnt, priority FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE (closed IS NULL or closed = 0) GROUP by priority ORDER by priority DESC');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
            } else {
                $row['priority'] = -1;
            }
            echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OpenedTaskP2') . '</td>';
            if ($row['priority'] == 2) {
                echo '<td class="value number">' . $row['cnt'] . '</td></tr>';
                $row = mysql_fetch_assoc($q);
            } else {
                echo '<td class="value number">0</td></tr>';
            }
            echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OpenedTaskP1') . '</td>';
            if ($row['priority'] == 1) {
                echo '<td class="value number">' . $row['cnt'] . '</td></tr>';
                $row = mysql_fetch_assoc($q);
            } else {
                echo '<td class="value number">0</td></tr>';
            }
            echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OpenedTaskP0') . '</td>';
            if ($row['priority'] == 0) {
                echo '<td class="value number">' . $row['cnt'] . '</td></tr>';
            } else {
                echo '<td class="value number">0</td></tr>';
            }
            mysql_free_result($q);

            $q = SQL_do('SELECT id, title, UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP( created ) AS age FROM ' . $GLOBALS['table_prefix'] . 'tasks ORDER BY created ASC LIMIT 1');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OldestTask') . '</td>';
                echo '<td class="value"><a href="index.php?cmd=show&amp;id=' . $row['id'] . '">' . htmlspecialchars($row['title']) . '</a></td></tr>';
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OldestTaskAge') . '</td>';
                echo '<td class="value number">' . round($row['age'] / (24 * 60 * 60), 1) . ' ' . LOCALE_get('Days') . '</td></tr>';
            }
            mysql_free_result($q);

            $q = SQL_do('SELECT AVG(UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP( created )) AS average FROM ' . $GLOBALS['table_prefix'] . 'tasks');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('AverageAge') . '</td>';
                echo '<td class="value number">' . round($row['average'] / (24 * 60 * 60), 1) . ' ' . LOCALE_get('Days') . '</td></tr>';
            }
            mysql_free_result($q);

            $q = SQL_do('SELECT id, title, UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP( created ) AS age FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE (closed IS NULL or closed = 0) ORDER BY created ASC LIMIT 1');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OldestOpenedTask') . '</td>';
                echo '<td class="value"><a href="index.php?cmd=show&amp;id=' . $row['id'] . '">' . htmlspecialchars($row['title']) . '</a></td></tr>';
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('OldestOpenedTaskAge') . '</td>';
                echo '<td class="value number">' . round($row['age'] / (24 * 60 * 60), 1) . ' ' . LOCALE_get('Days') . '</td></tr>';
            }
            mysql_free_result($q);

            $q = SQL_do('SELECT AVG(UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP( created )) AS average FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE (closed IS NULL or closed = 0)');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('AverageOpenedAge') . '</td>';
                echo '<td class="value number">' . round($row['average'] / (24 * 60 * 60), 1) . ' ' . LOCALE_get('Days') . '</td></tr>';
            }
            mysql_free_result($q);

            $q = SQL_do('SELECT AVG(UNIX_TIMESTAMP(closed) - UNIX_TIMESTAMP(created)) AS average FROM ' . $GLOBALS['table_prefix'] . 'tasks WHERE NOT (closed IS NULL or closed = 0)');
            if (mysql_num_rows($q) > 0) {
                $row = mysql_fetch_assoc($q);
                echo '<tr class="nopriority"><td class="name">' . LOCALE_get('AverageCloseAge') . '</td>';
                echo '<td class="value number">' . round($row['average'] / (24 * 60 * 60), 1) . ' ' . LOCALE_get('Days') . '</td></tr>';
            }
            mysql_free_result($q);

            echo '</tbody></table>';
            $cmd = '';
            break;
        case 'export':
            echo LOCALE_get('ExportFormats');
            echo '<ul>';
            echo '<li><a href="index.php?cmd=export_csv">' . LOCALE_get('CSVExport') . '</a></li>';
            echo '<li><a href="index.php?cmd=export_vcal">' . LOCALE_get('vCalExport') . '</a></li>';
            echo '</ul>';
            $cmd = '';
            break;
        case 'export_csv':
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="ukolovnik.csv"');

            $q = SQL_do('SELECT id,category,UNIX_TIMESTAMP(created) AS created,priority,title,description,UNIX_TIMESTAMP(closed) AS closed FROM ' . $GLOBALS['table_prefix'] . 'tasks ' . $filter . ' ORDER BY priority DESC, created ASC');
            echo "priority,title,description,category,created,closed\n";
            if (mysql_num_rows($q) > 0) {
                while ($row = mysql_fetch_assoc($q)) {
                    echo $row['priority'];
                    echo ',';
                    echo '"' . $row['title'] . '"';
                    echo ',';
                    echo '"' . $row['description'] . '"';
                    echo ',';
                    echo $row['category'];
                    echo ',';
                    echo $row['created'];
                    echo ',';
                    echo $row['closed'];
                    echo "\n";
                }
            }
            mysql_free_result($q);
            $cmd = '';
            break;
        case 'export_vcal':
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="ukolovnik.vcs"');

            $q = SQL_do('SELECT id,category,UNIX_TIMESTAMP(created) AS created,priority,title,description,UNIX_TIMESTAMP(closed) AS closed FROM ' . $GLOBALS['table_prefix'] . 'tasks ' . $filter . ' ORDER BY priority DESC, created ASC');
            echo "BEGIN:VCALENDAR\r\n";
            echo "VERSION:1.0\r\n";
            if (mysql_num_rows($q) > 0) {
                while ($row = mysql_fetch_assoc($q)) {
                    echo "BEGIN:VTODO\r\n";
                    echo 'PRIORITY:' . $row['priority'] . "\r\n";
                    echo 'CATEGORIES:' . $row['category'] . "\r\n";
                    echo 'SUMMARY;CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:' . STRING_quoted_printable($row['title']) . "\r\n";
                    echo 'DESCRIPTION;CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:' . STRING_quoted_printable($row['description']) . "\r\n";
                    echo 'CREATED:' . STRING_format_date_vcal($row['created']) . "\r\n";
                    if (!is_null($row['closed'])) {
                        echo 'COMPLETED:' . STRING_format_date_vcal($row['closed']) . "\r\n";
                        echo "STATUS:COMPLETED\r\n";
                        echo "PERCENT-COMPLETE:100\r\n";
                    }
                    echo "END:VTODO\r\n";
                }
            }
            echo "END:VCALENDAR\r\n";
            mysql_free_result($q);
            $cmd = '';
            break;
        default:
            HTML_message('error', LOCALE_get('UnknownCommand'));
            $cmd = '';
            break;
    }
}
if ($show_html) {
    HTML_footer();
}
?>
