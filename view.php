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
 * Prints an instance of mod_videotime.
 *
 * @package     mod_videotime
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\output\next_activity_button;
use mod_videotime\videotime_instance;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $USER;

// Course_module ID, or.
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$v  = optional_param('v', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('videotime', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('videotime', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($v) {
    $moduleinstance = $DB->get_record('videotime', ['id' => $v], '*', MUST_EXIST);
    $course         = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('videotime', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error('invalidcoursemodule');
}

$moduleinstance = videotime_instance::instance_by_id($moduleinstance->id);

$PAGE->set_context($moduleinstance->get_context());

require_login($course, true, $cm);

require_capability('mod/videotime:view', $moduleinstance->get_context());

videotime_view($moduleinstance, $course, $cm, $moduleinstance->get_context());

$PAGE->set_url('/mod/videotime/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('mod_videotime');

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($moduleinstance->name), 2);
if (!$moduleinstance->vimeo_url) {
    \core\notification::error(get_string('vimeo_url_missing', 'videotime'));
} else {
    echo $renderer->render($moduleinstance);
}
echo $OUTPUT->footer();
