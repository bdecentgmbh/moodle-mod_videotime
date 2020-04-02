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
 * Prints Video Time report.
 *
 * @package     mod_videotime
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $USER;

// Course_module ID, or.
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$v  = optional_param('v', 0, PARAM_INT);

$download = optional_param('download', '', PARAM_ALPHA);

if ($id) {
    $cm             = get_coursemodule_from_id('videotime', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('videotime', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($v) {
    $moduleinstance = $DB->get_record('videotime', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('videotime', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error('invalidcoursemodule');
}

require_login($course, true, $cm);

if (!videotime_has_pro()) {
    throw new \Exception('Video Time Pro not installed.');
}

$modulecontext = context_module::instance($cm->id);

require_capability('mod/videotime:view_report', $modulecontext);

$PAGE->set_url('/mod/videotime/report.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$table = new \videotimeplugin_pro\sessions_report_table($cm->id, $download);
$table->define_baseurl($PAGE->url);
$table->is_downloadable(true);
$table->show_download_buttons_at([TABLE_P_BOTTOM]);

$form = new \videotimeplugin_pro\form\report_settings_form($PAGE->url);

$pagesize = get_user_preferences('videotimeplugin_pro_pagesize', 25);

if ($data = $form->get_data()) {
    $pagesize = $data->pagesize;
    set_user_preference('videotimeplugin_pro_pagesize', $data->pagesize);
} else {
    $form->set_data(['pagesize' => $pagesize]);
}

// If downloading get all records.
if ($table->is_downloading()) {
    $pagesize = -1;
}

ob_start();
$table->out($pagesize, true);
$tablehtml = ob_get_contents();
ob_end_clean();

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($moduleinstance->name), 2);
echo $tablehtml;
$form->display();
echo $OUTPUT->footer();
