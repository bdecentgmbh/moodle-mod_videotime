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
 * Get videotime instance object for cm.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\external;

defined('MOODLE_INTERNAL') || die();

use external_api;
use mod_videotime\videotime_instance;

require_once($CFG->libdir.'/externallib.php');
require_once($CFG->dirroot.'/mod/videotime/lib.php');

/**
 * Get videotime instance object for cm.
 */
trait get_videotime {

    /**
     * Describes the parameters for get_videotime.
     *
     * @return external_function_parameters
     */
    public static function get_videotime_parameters() {
        return new \external_function_parameters([
            'cmid' => new \external_value(PARAM_INT, 'Course module ID', VALUE_REQUIRED)
        ]);
    }

    /**
     * Return videotime instance
     *
     * @param  int $cmid The videotime course module id
     * @return stdClass module instance
     */
    public static function get_videotime($cmid) {
        $params = external_api::validate_parameters(self::get_videotime_parameters(), [
            'cmid' => $cmid
        ]);

        $context = \context_module::instance($params['cmid']);
        external_api::validate_context($context);

        $cm = get_coursemodule_from_id('videotime', $params['cmid'], 0, false, MUST_EXIST);

        $moduleinstance = videotime_instance::instance_by_id($cm->instance);

        return $moduleinstance->to_record();
    }

    /**
     * Describes the get_videotime return value.
     *
     * @return external_single_structure
     */
    public static function get_videotime_returns() {
        return videotime_instance::get_external_description();
    }
}
