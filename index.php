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

require(__DIR__ . '/../../config.php');

require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
require_course_login($course);

$coursecontext = context_course::instance($course->id);

$event = \mod_videotime\event\course_module_instance_list_viewed::create([
    'context' => $coursecontext,
]);
$event->add_record_snapshot('course', $course);
$event->trigger();
$usesections = course_format_uses_sections($course->format);

$PAGE->set_url('/mod/videotime/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

$modulenameplural = get_string('modulenameplural', 'mod_videotime');
echo $OUTPUT->heading($modulenameplural);

$videotimes = get_all_instances_in_course('videotime', $course);

if (empty($videotimes)) {
    notice(get_string('nonewmodules', 'mod_videotime'), new moodle_url('/course/view.php', ['id' => $course->id]));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == 'weeks') {
    $table->head  = [get_string('week'), get_string('name')];
    $table->align = ['center', 'left'];
} else if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_' . $course->format);
    $table->head  = [$strsectionname, get_string('name')];
    $table->align = ['center', 'left', 'left', 'left'];
} else {
    $table->head  = [get_string('name')];
    $table->align = ['left', 'left', 'left'];
}

if (videotime_has_pro() && has_capability('mod/videotime:view_report', $coursecontext)) {
    // Header for viewing report links.
    $table->head[] = get_string('view_report', 'videotime');
}

foreach ($videotimes as $videotime) {
    if (!$videotime->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/videotime/view.php', ['id' => $videotime->coursemodule]),
            format_string($videotime->name, true),
            ['class' => 'dimmed']
        );
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/videotime/view.php', ['id' => $videotime->coursemodule]),
            format_string($videotime->name, true)
        );
    }

    if ($course->format == 'weeks') {
        $data = [
            $videotime->section,
            $link,
        ];
    } else if ($usesections) {
        $data = [
            get_section_name($course, $videotime->section),
            $link,
        ];
    } else {
        $data = [$link];
    }

    if (videotime_has_pro() && has_capability('mod/videotime:view_report', $coursecontext)) {
        $url = new moodle_url('/mod/videotime/report.php', ['id' => $videotime->coursemodule]);
        $data[] = \html_writer::link($url, $DB->get_field(
            \videotimeplugin_pro\session::TABLE,
            'COUNT(DISTINCT uuid)',
            ['module_id' => $videotime->coursemodule]
        ) . ' ' . get_string('views', 'videotime'));
    }

    $table->data[] = $data;
}

echo html_writer::table($table);
echo $OUTPUT->footer();
