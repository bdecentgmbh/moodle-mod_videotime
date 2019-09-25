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

$moduleinstance = videotime_populate_with_defaults($moduleinstance);

$modulecontext = context_module::instance($cm->id);
$PAGE->set_context($modulecontext);

require_login($course, true, $cm);

require_capability('mod/videotime:view', $modulecontext);

// Completion and trigger events.
videotime_view($moduleinstance, $course, $cm, $modulecontext);

$PAGE->set_url('/mod/videotime/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('mod_videotime');

// Watch time tracking is only available in pro.
$resume_time = 0;
$next_activity_url = null;
$sessiondata = false;
$next_activity_button = null;

if (videotime_has_pro()) {
    $sessions = \videotimeplugin_pro\module_sessions::get($cm->id, $USER->id);

    $session = \videotimeplugin_pro\session::create_new($cm->id, $USER);
    $sessiondata = $session->jsonSerialize();
    if ($moduleinstance->resume_playback) {
        $resume_time = $sessions->get_current_watch_time();
    }
    $next_activity_button = new next_activity_button(cm_info::create($cm));
    if ($moduleinstance->next_activity_auto) {
        if (!$next_activity_button->is_restricted() && $next_cm = $next_activity_button->get_next_cm()) {
            $next_activity_url = $next_cm->url->out(false);
        }
    }
}

$PAGE->requires->js_call_amd('mod_videotime/videotime', 'init', [$sessiondata, 5, videotime_has_pro(),
    $moduleinstance, $cm->id, $resume_time, $next_activity_url]);

$moduleinstance->intro  = file_rewrite_pluginfile_urls($moduleinstance->intro, 'pluginfile.php', $modulecontext->id,
    'mod_videotime', 'intro', null);
$moduleinstance->video_description = file_rewrite_pluginfile_urls($moduleinstance->video_description, 'pluginfile.php',
    $modulecontext->id, 'mod_videotime', 'video_description', 0);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($moduleinstance->name), 2);
if (!$moduleinstance->vimeo_url) {
    \core\notification::error(get_string('vimeo_url_missing', 'videotime'));
} else {

    $context = [
        'instance' => $moduleinstance,
        'cmid' => $cm->id
    ];

    if (videotime_has_pro() && $next_activity_button) {
        $context['next_activity_button_html'] = $renderer->render($next_activity_button);
    }

    echo $OUTPUT->render_from_template('mod_videotime/view', $context);
}
echo $OUTPUT->footer();
