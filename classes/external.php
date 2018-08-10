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
require_once($CFG->dirroot . '/mod/videotime/lib.php');

class external extends \external_api
{
    public static function record_watch_time_parameters()
    {
        return new \external_function_parameters([
            'session_id' => new \external_value(PARAM_INT, 'Session ID', VALUE_REQUIRED),
            'time' => new \external_value(PARAM_INT, 'Time in seconds watched on video', VALUE_REQUIRED)
        ]);
    }

    public static function record_watch_time($session_id, $time)
    {
        // Check if pro is installed. This is a pro feature.
        if (!videotime_has_pro()) {
            return ['success' => true];
        }

        $params = self::validate_parameters(self::record_watch_time_parameters(), [
            'session_id' => $session_id,
            'time' => $time
        ]);
        $session_id = $params['session_id'];
        $time = $params['time'];

        // Session should exist and be created when user visits view.php.
        if (!$session = \videotimeplugin_pro\session::get_one_by_id($session_id)) {
            throw new \videotimeplugin_pro\exception\session_not_found();
        }

        $session->set_time($time);
        $session->persist();

        return ['success' => true];
    }

    public static function record_watch_time_returns() {
        return new \external_single_structure([
            'success' => new \external_value(PARAM_BOOL)
        ]);
    }
}
