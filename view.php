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
 * Prints an instance of mod_vimeo.
 *
 * @package     mod_vimeo
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$v  = optional_param('v', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('vimeo', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('vimeo', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($v) {
    $moduleinstance = $DB->get_record('vimeo', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('vimeo', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', mod_vimeo));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_vimeo\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('vimeo', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/vimeo/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->requires->js_call_amd('mod_vimeo/vimeo', 'init');

$moduleinstance->intro  = file_rewrite_pluginfile_urls($moduleinstance->intro, 'pluginfile.php', $modulecontext->id, 'mod_vimeo', 'intro', null);
$moduleinstance->video_description = file_rewrite_pluginfile_urls($moduleinstance->video_description, 'pluginfile.php', $modulecontext->id, 'mod_vimeo', 'video_description', 0);

echo $OUTPUT->header();
if (!$moduleinstance->vimeo_url) {
    \core\notification::error(get_string('vimeo_url_missing', 'vimeo'));
} else {
    echo $OUTPUT->render_from_template('mod_vimeo/view', [
        'instance' => $moduleinstance
    ]);
}
echo $OUTPUT->footer();
