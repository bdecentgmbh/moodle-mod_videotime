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
 * @package     mod_videotime
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\event;

use core\event\admin_settings_changed;

defined('MOODLE_INTERNAL') || die();

class settings_observer {

    /**
     * @param admin_settings_changed $event
     */
    public static function changed(admin_settings_changed $event) {
        global $DB;

        if (during_initial_install()) {
            return;
        }

        $force_fields = [];

        foreach ($event->other['olddata'] as $full_name => $value) {
            if (strpos($full_name, 'videotime') !== false && self::string_ends_with($full_name, '_force')) {
                $force_name = str_replace('s_videotime_', '', $full_name);

                if (get_config('videotime', $force_name)) {
                    $name = str_replace('s_videotime_', '', $full_name);
                    $name = str_replace('_force', '', $name);
                    $force_fields[$name] = get_config('videotime', $name);
                }
            }
        }

        if (count($force_fields) > 0) {
            $sets = [];
            foreach ($force_fields as $name => $value) {
                $sets[] = $name . ' = :' . $name;
            }
            $DB->execute('UPDATE {videotime} SET ' . implode(', ', $sets), $force_fields);
        }
    }

    /**
     * Check if a string ends with another string.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function string_ends_with($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
