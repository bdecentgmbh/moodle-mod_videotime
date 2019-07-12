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
 * Redirects the user to Video Time instance.
 *
 * @package     mod_videotime
 * @copyright   2019 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', null, PARAM_INT);

$cm = get_coursemodule_from_id('videotime', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, false, $cm);

$PAGE->set_url('/mod/videotime/grade.php', ['id' => $cm->id]);

if (videotime_has_pro() && $userid) {
    redirect(new moodle_url('/mod/videotime/report.php', ['id' => $cm->id]));
} else {
    redirect(new moodle_url('/mod/videotime/view.php', ['id' => $cm->id]));
}
