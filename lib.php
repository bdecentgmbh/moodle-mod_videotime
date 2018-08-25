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
 * Library of interface functions and constants.
 *
 * @package     mod_videotime
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function videotime_supports($feature) {
    switch ($feature) {
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        default:
            return null;
    }
}

/**
 * Get all module fields that are to be used as player.js options.
 *
 * @return array
 */
function videotime_get_emnbed_option_names() {
    return [
        'responsive',
        'autoplay',
        'byline',
        'color',
        'height',
        'maxheight',
        'maxwidth',
        'muted',
        'playsinline',
        'portrait',
        'speed',
        'title',
        'transparent',
        'width',
    ];
}

/**
 * Get all embed options for module instance.
 *
 * @param $moduleinstance
 * @return stdClass
 */
function videotime_get_embed_options($moduleinstance) {
    $options = new \stdClass();
    foreach (videotime_get_emnbed_option_names() as $name) {
        if (isset($moduleinstance->$name)) {
            $options->$name = $moduleinstance->$name;
        }
    }
    return $options;
}

/**
 * Saves a new instance of the mod_videotime into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_videotime_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function videotime_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $context = context_module::instance($moduleinstance->coursemodule);

    $moduleinstance->timecreated = time();

    $moduleinstance = videotime_process_video_description($moduleinstance);

    $id = $DB->insert_record('videotime', $moduleinstance);

    if (!empty($moduleinstance->preview_image)) {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_videotime', 'preview_image');
        file_save_draft_area_files($moduleinstance->preview_image, $context->id, 'mod_videotime', 'preview_image',
            0, array('subdirs' => 0, 'maxfiles' => 1));
    }

    return $id;
}

/**
 * Updates an instance of the mod_videotime in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_videotime_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 * @throws \dml_exception
 */
function videotime_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $context = context_module::instance($moduleinstance->coursemodule);

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    $moduleinstance = videotime_process_video_description($moduleinstance);

    if (!empty($moduleinstance->preview_image)) {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_videotime', 'preview_image');
        file_save_draft_area_files($moduleinstance->preview_image, $context->id, 'mod_videotime', 'preview_image',
            0, array('subdirs' => 0, 'maxfiles' => 1));
    }

    return $DB->update_record('videotime', $moduleinstance);
}

/**
 * Removes an instance of the mod_videotime from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function videotime_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('videotime', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('videotime', array('id' => $id));

    return true;
}

/**
 * @param $moduleinstance
 * @return mixed
 */
function videotime_process_video_description($moduleinstance) {
    $modcontext = context_module::instance($moduleinstance->coursemodule);
    $video_description = $moduleinstance->video_description;
    $moduleinstance->video_description = file_save_draft_area_files($video_description['itemid'], $modcontext->id,
        'mod_videotime', 'video_description', 0,
        array('subdirs'=>true), $video_description['text']);
    $moduleinstance->video_description_format = $video_description['format'];
    return $moduleinstance;
}

/**
 * Obtains the automatic completion state for this forum based on any conditions
 * in forum settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 * @throws \dml_exception
 */
function videotime_get_completion_state($course,$cm,$userid,$type) {
    global $DB;

    // Get forum details
    $videotime = $DB->get_record('videotime', ['id' => $cm->instance], '*', MUST_EXIST);

    // Completion settings are pro features.
    if (!videotime_has_pro()) {
        return $type;
    }

    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

    if (!$videotime->completion_on_view_time && !$videotime->completion_on_finish && !$videotime->completion_on_percent) {
        // Completion options are not enabled so just return $type
        return $type;
    }

    $sessions = \videotimeplugin_pro\module_sessions::get($cm->id, $user->id);

    // Check if watch time is required.
    if ($videotime->completion_on_view_time) {
        // If time was never set return false.
        if (!$videotime->completion_on_view_time_second) {
            return false;
        }
        // Check if total session time is over the required duration.
        if ($sessions->get_total_time()
            < $videotime->completion_on_view_time_second) {
            return false;
        }
    }

    if ($videotime->completion_on_percent) {
        // If percent value was never set return false.
        if (!$videotime->completion_on_percent_value) {
            return false;
        }
        // Check if watch percentage is met.
        if (($sessions->get_percent()*100) < $videotime->completion_on_percent_value) {
            return false;
        }
    }

    // Check if video completion is required.
    if ($videotime->completion_on_finish) {
        if (!$sessions->is_finished()) {
            return false;
        }
    }

    return true;
}

/**
 * @param $cm_id
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function videotime_update_completion($cm_id) {
    global $DB, $CFG;

    require_once($CFG->libdir.'/completionlib.php');

    $cm = get_coursemodule_from_id('videotime', $cm_id, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $moduleinstance = $DB->get_record('videotime', array('id' => $cm->instance), '*', MUST_EXIST);

    $completion = new \completion_info($course);
    if($completion->is_enabled($cm) && ($moduleinstance->completion_on_view_time || $moduleinstance->completion_on_finish)) {
        $completion->update_state($cm,COMPLETION_COMPLETE);
    }
}

/**
 * File serving callback
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file was not found, just send the file otherwise and do not return anything
 */
function videotime_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea == 'video_description' || $filearea == 'intro') {

        $relativepath = implode('/', $args);

        $fullpath = "/$context->id/mod_videotime/$filearea/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, null, 0, $forcedownload, $options);
    }
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $videotime  Video Time object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 */
function videotime_view($videotime, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $videotime->id
    );

    $event = \mod_videotime\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('videotime', $videotime);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Check if Video Time Pro is installed.
 *
 * @return bool
 */
function videotime_has_pro() {
    global $CFG;

    if (isset($CFG->disable_videotime_pro) && $CFG->disable_videotime_pro) {
        return false;
    }
    return array_key_exists('pro', core_component::get_plugin_list('videotimeplugin'));
}

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings
 * @param navigation_node $videtimenode
 * @return void
 * @throws \coding_exception
 * @throws moodle_exception
 */
function videotime_extend_settings_navigation($settings, $videtimenode) {
    global $PAGE, $CFG;

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $videtimenode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (videotime_has_pro() && has_capability('mod/videotime:view_report', $PAGE->cm->context)) {
        $node = navigation_node::create(get_string('report'),
            new moodle_url('/mod/videotime/report.php', array('id' => $PAGE->cm->id)),
            navigation_node::TYPE_SETTING, null, 'mod_videotime_report',
            new pix_icon('t/grades', ''));
        $videtimenode->add_node($node, $beforekey);
    }
}

/**
 * This function extends the course navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function videotime_extend_navigation_course($navigation, $course, $context) {
    $node = $navigation->get('coursereports');
    if (videotime_has_pro() && has_capability('mod/videotime:view_report', $context)) {
        $url = new moodle_url('/mod/videotime/index.php', ['id' => $course->id]);
        $node->add(get_string('pluginname', 'videotime'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}