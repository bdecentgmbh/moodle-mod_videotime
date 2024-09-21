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
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\output\next_activity_button;
use mod_videotime\videotime_instance;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

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
    throw new moodle_exception('invalidcoursemodule', 'mod_videotime');
}

$token = required_param('token', PARAM_TEXT);

$context = context_module::instance($cm->id);
$PAGE->set_context($context);

if (
    $user = $DB->get_record_sql("
    SELECT u.*
      FROM {user} u
      JOIN {external_tokens} t ON t.userid = u.id
      JOIN {external_services} s ON s.id = t.externalserviceid
     WHERE
          t.token = :token
          AND s.shortname = :shortname", [
    'contextid' => $context->id,
    'shortname' => MOODLE_OFFICIAL_MOBILE_SERVICE,
    'token' => $token,
    ])
) {
    \core\session\manager::login_user($user);
} else {
    require_login($course, true, $cm);
    throw new moodle_exception('notoken');

    require_capability('mod/videotime:view', $context);
}

$PAGE->set_url('/mod/videotime/player.php', ['id' => $cm->id]);
$PAGE->set_pagelayout('popup');
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->add_body_class('limitedwidth');

if (class_exists('core\\output\\activity_header')) {
    $PAGE->activityheader->disable();
}
$moduleinstance = videotime_instance::instance_by_id($moduleinstance->id, $token);

$renderer = $PAGE->get_renderer('mod_videotime');

// Allow any subplugin to override video time instance output.
foreach (\core_component::get_component_classes_in_namespace(null, 'videotime\\instance') as $fullclassname => $classpath) {
    if (is_subclass_of($fullclassname, videotime_instance::class)) {
        if ($override = $fullclassname::get_instance($moduleinstance->id)) {
            $moduleinstance = $override;
        }
        if ($override = $fullclassname::get_renderer($moduleinstance->id)) {
            $renderer = $override;
        }
    }
}

echo $OUTPUT->header();

foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
    if ($player = component_callback("videotimeplugin_$name", 'embed_player', [$moduleinstance->to_record()], null)) {
        break;
    }
}

if (empty($player)) {
    \core\notification::error(get_string('vimeo_url_missing', 'videotime'));
} else {
    $moduleinstance->next_activity_button_html = '';
    echo $renderer->render($moduleinstance);
}
echo $OUTPUT->footer();
