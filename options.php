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
 * Prints player options form.
 *
 * @package     mod_videotime
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;
use videotimeplugin_pro\session;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

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
    $moduleinstance = $DB->get_record('videotime', ['id' => $n], '*', MUST_EXIST);
    $course         = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('videotime', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('invalidcoursemodule', 'mod_videotime');
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

require_capability('moodle/course:manageactivities', $modulecontext);

$PAGE->set_url('/mod/videotime/options.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
if (class_exists('core\\output\\activity_header')) {
    $PAGE->activityheader->disable();
}
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$moduleinstance = videotime_instance::instance_by_id($moduleinstance->id);

foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
    if ($player = component_callback("videotimeplugin_$name", 'embed_player', [$moduleinstance->to_record()], null)) {
        $classname = "\\videotimeplugin_$name\\form\options";
        $form = new $classname($PAGE->url->out(), ['instance' => $moduleinstance->to_record()]);
    }
}

$returnurl = new moodle_url('/mod/videotime/view.php', ['id' => $cm->id]);
if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $defaults = [];
    foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
        $classname = "\\videotimetab_$name\\tab";
        $classname::data_preprocessing($defaults, $cm->instance);
    }
    foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
        component_callback("videotimeplugin_$name", 'data_preprocessing', [&$defaults, $cm->instance]);
    }
    $moduleinstance = ['coursemodule' => $cm->id] + (array) $data + (array) $moduleinstance->to_record() + $defaults;
    videotime_update_instance((object) $moduleinstance, $form);
    redirect($returnurl);
}

$defaults = [];
foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
    component_callback("videotimeplugin_$name", 'data_preprocessing', [&$defaults, $cm->instance]);
}

$form->set_data((array)$moduleinstance->to_record() + $defaults);

echo $OUTPUT->header();
if (!class_exists('core\\output\\activity_header')) {
    echo $OUTPUT->heading(format_string($moduleinstance->name), 2);
}

$form->display();
echo $OUTPUT->footer();
