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
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $USER;

// Course_module ID, or.
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$v  = optional_param('v', 0, PARAM_INT);

$action = required_param('action', PARAM_TEXT);
$return = required_param('return', PARAM_URL);
$userid = required_param('userid', PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('videotime', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('videotime', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($v) {
    $moduleinstance = $DB->get_record('videotime', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('videotime', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('invalidcoursemodule', 'mod_videotime');
}
require_login($course, true, $cm);

if (!is_siteadmin()) {
    throw new coding_exception('Only admins can view this page.');
}

if (!videotime_has_pro()) {
    throw new \Exception('Video Time Pro not installed.');
}

$modulecontext = context_module::instance($cm->id);
$PAGE->set_context($modulecontext);
$PAGE->set_url('/mod/videotime/action.php', [
    'id' => $id,
    'v' => $v,
    'action' => $action,
    'return' => $return,
    'userid' => $userid
]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

$task = null;

switch ($action) {
    case 'delete_session_data':
        $task = new \videotimeplugin_pro\task\delete_session_data();
        $task->set_custom_data([
            'user_id' => $userid,
            'module_id' => $cm->id
        ]);
        break;
}

if (!$task) {
    throw new coding_exception('Task could not be created.');
}

\core\task\manager::queue_adhoc_task($task);
\core\notification::success(get_string('taskscheduled', 'videotime') . ': ' . $action);

redirect($return);
