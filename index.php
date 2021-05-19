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
 * Display information about all the mod_videotime modules in the requested course.
 *
 * @package     mod_videotime
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_course_login($course);

$coursecontext = context_course::instance($course->id);

$event = \mod_videotime\event\course_module_instance_list_viewed::create(array(
    'context' => $coursecontext
));
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url('/mod/videotime/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

$modulenameplural = get_string('modulenameplural', 'mod_videotime');
echo $OUTPUT->heading($modulenameplural);

$videotimes = get_all_instances_in_course('videotime', $course);

if (empty($videotimes)) {
    notice(get_string('nonewmodules', 'mod_videotime'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left', 'left', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left', 'left', 'left');
}

if (videotime_has_pro() && has_capability('mod/videotime:view_report', $coursecontext)) {
    // Header for viewing report links.
    $table->head[] = '';
}

foreach ($videotimes as $videotime) {
    if (!$videotime->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/videotime/view.php', array('id' => $videotime->coursemodule)),
            format_string($videotime->name, true),
            array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/videotime/view.php', array('id' => $videotime->coursemodule)),
            format_string($videotime->name, true));
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $data = array($videotime->section, $link);
    } else {
        $data = array($link);
    }

    if (videotime_has_pro() && has_capability('mod/videotime:view_report', $coursecontext)) {
        $url = new moodle_url('/mod/videotime/report.php', ['id' => $videotime->coursemodule]);
        $data[] = \html_writer::link($url, get_string('view_report', 'videotime'));
    }

    $table->data[] = $data;
}

echo html_writer::table($table);
echo $OUTPUT->footer();
