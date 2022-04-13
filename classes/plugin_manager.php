<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class that handles the display and configuration of the list of tab plugins.
 *
 * @package   mod_videotime
 * @copyright 2021 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime;

defined('MOODLE_INTERNAL') || die();

use context_system;
use core_component;
use core_plugin_manager;
use flexible_table;
use html_writer;
use moodle_url;
use pix_icon;

require_once($CFG->libdir . '/adminlib.php');

/**
 * Class that handles the display and configuration of the list of tab plugins.
 *
 * @package   mod_videotime
 * @copyright 2021 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_manager {

    /** @var object the url of the manage plugin page */
    private $pageurl;
    /** @var string any error from the current action */
    private $error = '';
    /** @var string either submission or feedback */
    private $subtype = '';

    /**
     * Constructor for this videotime plugin manager
     * @param string $subtype - only videotimetab implemented
     */
    public function __construct($subtype) {
        $this->pageurl = new moodle_url('/mod/videotime/adminmanageplugins.php', array('subtype' => $subtype));
        $this->subtype = $subtype;
    }


    /**
     * Return a list of plugins sorted by the order defined in the admin interface
     *
     * @return array The list of plugins
     */
    public function get_sorted_plugins_list() {
        $names = core_component::get_plugin_list($this->subtype);

        $result = array();
        $disabled = array();

        foreach ($names as $name => $path) {
            $classname = '\\' . $this->subtype . '_' . $name . '\\tab';
            if (!empty(get_config($this->subtype . '_' . $name, 'enabled')) && empty($classname::added_dependencies())) {
                $idx = get_config($this->subtype . '_' . $name, 'sortorder');
                if (!$idx) {
                    $idx = 0;
                }
                while (array_key_exists($idx, $result)) {
                    $idx += 1;
                }
                $result[$idx] = $name;
            } else {
                $disabled[] = $name;
            }
        }
        ksort($result);

        return array_merge($result, $disabled);
    }


    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $plugin URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     */
    private function format_icon_link($action, $plugin, $icon, $alt) {
        global $OUTPUT;

        $url = $this->pageurl;

        if ($action === 'delete') {
            $url = core_plugin_manager::instance()->get_uninstall_url($this->subtype.'_'.$plugin, 'manage');
            if (!$url) {
                return '&nbsp;';
            }
            return html_writer::link($url, get_string('uninstallplugin', 'core_admin'));
        }

        return $OUTPUT->action_icon(new moodle_url($url,
                array('action' => $action, 'plugin' => $plugin, 'sesskey' => sesskey())),
                new pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                null, array('title' => $alt)) . ' ';
    }

    /**
     * Write the HTML for the submission plugins table.
     *
     * @return None
     */
    private function view_plugins_table() {
        global $OUTPUT, $CFG;
        require_once($CFG->libdir . '/tablelib.php');

        // Set up the table.
        $this->view_header();
        $table = new flexible_table($this->subtype . 'pluginsadminttable');
        $table->define_baseurl($this->pageurl);
        $table->define_columns(array('pluginname', 'version', 'hideshow', 'order',
                'settings', 'status', 'uninstall'));
        $table->define_headers(array(get_string($this->subtype . 'pluginname', 'videotime'),
                get_string('version'), get_string('hideshow', 'videotime'),
                get_string('order'), get_string('settings'), get_string('status'), get_string('uninstallplugin', 'core_admin')));
        $table->set_attribute('id', $this->subtype . 'plugins');
        $table->set_attribute('class', 'admintable generaltable');
        $table->setup();

        $plugins = $this->get_sorted_plugins_list();
        $shortsubtype = substr($this->subtype, strlen('videotime'));

        foreach ($plugins as $idx => $plugin) {
            $row = array();
            $class = '';

            $row[] = get_string('pluginname', $this->subtype . '_' . $plugin);
            $row[] = get_config($this->subtype . '_' . $plugin, 'version');

            $classname = '\\' . $this->subtype . '_' . $plugin . '\\tab';
            $visible = !empty(get_config($this->subtype . '_' .$plugin, 'enabled')) && empty($classname::added_dependencies());

            if ($visible) {
                $row[] = $this->format_icon_link('hide', $plugin, 't/hide', get_string('disable'));
            } else if ($classname::added_dependencies()) {
                $row[] = '';
            } else {
                $row[] = $this->format_icon_link('show', $plugin, 't/show', get_string('enable'));
                $class = 'dimmed_text';
            }

            $movelinks = '';
            if (!$idx == 0) {
                $movelinks .= $this->format_icon_link('moveup', $plugin, 't/up', get_string('up'));
            } else {
                $movelinks .= $OUTPUT->spacer(array('width' => 16));
            }
            if ($idx != count($plugins) - 1) {
                $movelinks .= $this->format_icon_link('movedown', $plugin, 't/down', get_string('down'));
            }
            $row[] = $movelinks;

            $exists = file_exists($CFG->dirroot . '/mod/videotime/' . $shortsubtype . '/' . $plugin . '/settings.php');
            if ($row[1] != '' && $exists) {
                $row[] = html_writer::link(new moodle_url('/admin/settings.php',
                        array('section' => $this->subtype . '_' . $plugin)), get_string('settings'));
            } else {
                $row[] = '&nbsp;';
            }

            $row[] = $classname::added_dependencies();

            $row[] = $this->format_icon_link('delete', $plugin, 't/delete', get_string('uninstallplugin', 'core_admin'));

            $table->add_data($row, $class);
        }

        $table->finish_output();
        $this->view_footer();
    }

    /**
     * Write the page header
     *
     * @return None
     */
    private function view_header() {
        global $OUTPUT;
        admin_externalpage_setup('manage' . $this->subtype . 'plugins');
        // Print the page heading.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('manage' . $this->subtype . 'plugins', 'videotime'));
    }

    /**
     * Write the page footer
     *
     * @return None
     */
    private function view_footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the list of installed plugins
     *
     * @return None
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * Hide this plugin.
     *
     * @param string $plugin - The plugin to hide
     * @return string The next page to display
     */
    public function hide_plugin($plugin) {
        set_config('enabled', 0, $this->subtype . '_' . $plugin);
        core_plugin_manager::reset_caches();
        return 'view';
    }

    /**
     * Change the order of this plugin.
     *
     * @param string $plugintomove - The plugin to move
     * @param string $dir - up or down
     * @return string The next page to display
     */
    public function move_plugin($plugintomove, $dir) {
        // Get a list of the current plugins.
        $plugins = $this->get_sorted_plugins_list();

        $currentindex = 0;

        // Throw away the keys.
        $plugins = array_values($plugins);

        // Find this plugin in the list.
        foreach ($plugins as $key => $plugin) {
            if ($plugin == $plugintomove) {
                $currentindex = $key;
                break;
            }
        }

        // Make the switch.
        if ($dir == 'up') {
            if ($currentindex > 0) {
                $tempplugin = $plugins[$currentindex - 1];
                $plugins[$currentindex - 1] = $plugins[$currentindex];
                $plugins[$currentindex] = $tempplugin;
            }
        } else if ($dir == 'down') {
            if ($currentindex < (count($plugins) - 1)) {
                $tempplugin = $plugins[$currentindex + 1];
                $plugins[$currentindex + 1] = $plugins[$currentindex];
                $plugins[$currentindex] = $tempplugin;
            }
        }

        // Save the new normal order.
        foreach ($plugins as $key => $plugin) {
            set_config('sortorder', $key, $this->subtype . '_' . $plugin);
        }
        return 'view';
    }


    /**
     * Show this plugin.
     *
     * @param string $plugin - The plugin to show
     * @return string The next page to display
     */
    public function show_plugin($plugin) {
        $sortorder = 0;
        foreach (array_keys(core_component::get_plugin_list($this->subtype)) as $name) {
            if (get_config($this->subtype . '_' . $name, 'enabled')) {
                set_config('sortorder', $sortorder, $this->subtype . '_' . $name);
                $sortorder++;
            }
        }
        set_config('sortorder', $sortorder, $this->subtype . '_' . $plugin);
        set_config('enabled', 1, $this->subtype . '_' . $plugin);
        core_plugin_manager::reset_caches();
        return 'view';
    }


    /**
     * This is the entry point for this controller class.
     *
     * @param string $action - The action to perform
     * @param string $plugin - Optional name of a plugin type to perform the action on
     * @return None
     */
    public function execute($action, $plugin) {
        if ($action == null) {
            $action = 'view';
        }

        $this->check_permissions();

        // Process.
        if ($action == 'hide' && $plugin != null) {
            $action = $this->hide_plugin($plugin);
        } else if ($action == 'show' && $plugin != null) {
            $action = $this->show_plugin($plugin);
        } else if ($action == 'moveup' && $plugin != null) {
            $action = $this->move_plugin($plugin, 'up');
        } else if ($action == 'movedown' && $plugin != null) {
            $action = $this->move_plugin($plugin, 'down');
        }

        // View.
        if ($action == 'view') {
            $this->view_plugins_table();
        }
    }
}
