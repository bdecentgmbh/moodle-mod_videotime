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
 * Mobile output class for Video Time.
 *
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotime\output;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/videotime/lib.php");

use context_module;

/**
 * Mobile output class for Video Time.
 *
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * Returns the video time course view for the mobile app.
     * @param array $args Arguments from tool_mobile_get_content WS
     *
     * @return array       HTML, javascript and otherdata
     * @throws \required_capability_exception
     * @throws \coding_exception
     * @throws \require_login_exception
     * @throws \moodle_exception
     */
    public static function mobile_course_view($args) {
        global $OUTPUT, $DB, $CFG;

        $args = (object)$args;
        $cm = get_coursemodule_from_id('videotime', $args->cmid);

        // Capabilities check.
        require_login($args->courseid, false, $cm, true, true);

        $context = context_module::instance($cm->id);

        require_capability('mod/videotime:view', $context);

        $videotime = $DB->get_record('videotime', array('id' => $cm->instance));

        $videotime->name = format_string($videotime->name);
        list($videotime->intro, $videotime->introformat) =
            external_format_text($videotime->intro, $videotime->introformat, $context->id, 'mod_videotime', 'intro');

        $data = array(
            'instance' => $videotime,
            'cmid' => $cm->id,
            'has_pro' => videotime_has_pro()
        );

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_videotime/view_mobile', $data),
                ],
            ],
            'javascript' => file_get_contents($CFG->dirroot . '/mod/videotime/appjs/videotime.js'),
            'otherdata' => '',
        ];
    }

    /**
     * Return JavaScript needed for viewing videos.
     *
     * @param array $args
     */
    public static function view_init(array $args) {
        global $CFG;

        return [
            'javascript' => file_get_contents($CFG->dirroot . '/mod/videotime/appjs/player.js') .
                file_get_contents("$CFG->dirroot/mod/videotime/appjs/view_init.js")
        ];

    }
}
