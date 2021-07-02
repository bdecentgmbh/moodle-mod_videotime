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
 * Set VideoTime activity as viewed, trigger view event, etc.
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
 * Set VideoTime activity as viewed, trigger view event, etc.
 */
trait view_videotime {

    /**
     * Describes the parameters for view_videotime.
     *
     * @return external_function_parameters
     */
    public static function view_videotime_parameters() {
        return new \external_function_parameters([
            'cmid' => new \external_value(PARAM_INT, 'Course module ID.')
        ]);
    }

    /**
     * Record video time view event
     *
     * @param  int $cmid The videotime course module id
     * @return stdClass module instance
     */
    public static function view_videotime($cmid) {
        global $DB;

        $params = external_api::validate_parameters(self::view_videotime_parameters(), [
            'cmid' => $cmid
        ]);

        $cm = get_coursemodule_from_id('videotime', $params['cmid']);

        $moduleinstance = videotime_instance::instance_by_id($cm->instance);

        $course = $DB->get_record('course', ['id' => $cm->course]);
        $context = \context_module::instance($cm->id);
        external_api::validate_context($context);

        require_capability('mod/videotime:view', $context);

        // Trigger course_module_viewed event and completion.
        videotime_view($moduleinstance, $course, $cm, $context);

        return null;
    }

    /**
     * Describes the view_videotime return value.
     *
     * @return external_single_structure|null
     */
    public static function view_videotime_returns() {
        return null;
    }
}
