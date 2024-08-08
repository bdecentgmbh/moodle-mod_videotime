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
 * Plugin version and other meta-data are defined here.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\plugininfo;

use core\update\info;

/**
 * Plugin version and other meta-data are defined here.
 *
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class videotimeplugin extends \core\plugininfo\base {
    /**
     * If there are updates for this plugin available, returns them.
     *
     * Returns array of \core\update\info objects, if some update
     * is available. Returns null if there is no update available or if the update
     * availability is unknown.
     *
     * Populates the property $availableupdates on first call (lazy loading).
     *
     * @return array|null
     */
    public function available_updates() {
        global $CFG;

        $updates = parent::available_updates();

        switch ($this->name) {
            case 'pro':
                $info = [
                    'maturity' => MATURITY_STABLE,
                    'release' => '1.8.2',
                    'version' => 2024050702,
                ];
                break;
            case 'repository':
                $info = [
                    'maturity' => MATURITY_STABLE,
                    'release' => '1.8.2',
                ];
                if ($CFG->branch < 403) {
                    $info['version'] = 2024050601;
                } else {
                    $info['version'] = 2024050701;
                }
                break;
        }
        if (!empty($info) && $this->versiondb < $info['version']) {
            $updates['videotimeplugin_' . $this->name] = new info('videotimeplugin_' . $this->name, $info);
        }

        return $updates;
    }

    /**
     * Allow uninstall
     *
     * @return bool
     */
    public function is_uninstall_allowed() {
        return true;
    }

    /**
     * Loads plugin settings to the settings tree
     *
     * This function usually includes settings.php file in plugins folder.
     * Alternatively it can create a link to some settings page (instance of admin_externalpage)
     *
     * @param \part_of_admin_tree $adminroot
     * @param string $parentnodename
     * @param bool $hassiteconfig whether the current user has moodle/site:config capability
     */
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig || !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);

        if ($adminroot->fulltree) {
            $shortsubtype = substr($this->type, strlen('assign'));
            include($this->full_path('settings.php'));
        }

        $adminroot->add($this->type . 'plugins', $settings);
    }

    /**
     * Get name to identify section
     *
     * @return string
     */
    public function get_settings_section_name() {
        return $this->type . '_' . $this->name;
    }

    /**
     * Returns the information about plugin availability
     *
     * True means that the plugin is enabled. False means that the plugin is
     * disabled. Null means that the information is not available, or the
     * plugin does not support configurable availability or the availability
     * can not be changed.
     *
     * @return null|bool
     */
    public function is_enabled() {
        $function = "videotime_has_$this->name";
        if (function_exists($function) && !$function()) {
            return false;
        }

        return !empty(get_config($this->type . '_' . $this->name, 'enabled'));
    }
}
