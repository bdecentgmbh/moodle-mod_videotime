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
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

class external extends \external_api
{
    public static function record_watch_time_parameters()
    {
        return new \external_function_parameters([
            'user_id' => new \external_value(PARAM_INT, 'User watching video', VALUE_REQUIRED),
            'module_id' => new \external_value(PARAM_INT, 'Course module ID', VALUE_REQUIRED),
            'time' => new \external_value(PARAM_INT, 'Time in seconds watched on video', VALUE_REQUIRED)
        ]);
    }

    public static function record_watch_time($user_id, $module_id, $time)
    {
        $params = self::validate_parameters(self::record_watch_time_parameters(), [
            'user_id' => $user_id,
            'module_id' => $module_id,
            'time' => $time
        ]);
        $user_id = $params['user_id'];
        $module_id = $params['module_id'];
        $time = $params['time'];

        return ['success' => true];
    }

    public static function record_watch_time_returns() {
        return new \external_single_structure([
            'success' => new \external_value(PARAM_BOOL)
        ]);
    }
}
