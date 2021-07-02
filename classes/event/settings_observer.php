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
 * Settings observer event
 *
 * @package     mod_videotime
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\event;

use core\event\admin_settings_changed;

defined('MOODLE_INTERNAL') || die();

/**
 * Settings observer event
 *
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_observer {

    /**
     * Change setting
     *
     * @param admin_settings_changed $event
     */
    public static function changed(admin_settings_changed $event) {
        global $DB;

        if (during_initial_install()) {
            return;
        }

        $forcefields = [];

        foreach ($event->other['olddata'] as $fullname => $value) {
            if (strpos($fullname, 'videotime') !== false && self::string_ends_with($fullname, '_force')) {
                $forcename = str_replace('s_videotime_', '', $fullname);

                if (get_config('videotime', $forcename)) {
                    $name = str_replace('s_videotime_', '', $fullname);
                    $name = str_replace('_force', '', $name);
                    $forcefields[$name] = get_config('videotime', $name);
                }
            }
        }

        if (count($forcefields) > 0) {
            $sets = [];
            foreach ($forcefields as $name => $value) {
                $sets[] = $name . ' = :' . $name;
            }
            $DB->execute('UPDATE {videotime} SET ' . implode(', ', $sets), $forcefields);
        }
    }

    /**
     * Check if a string ends with another string.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function string_ends_with($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
