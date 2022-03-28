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
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

/**
 * Checks if Videotime supports a specific feature.
 *
 * Return if the plugin supports $feature.
 * http://localhost/moodle35/course/mod.php?sesskey=nzkiyHyS2D&sr=0&update=2
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function videotime_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        default:
            return null;
    }
}

/**
 * Update/create grade item for given data
 *
 * @category grade
 * @param stdClass $videotime A videotime instance with extra cmidnumber property
 * @param mixed $grades Optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return object grade_item
 */
function videotime_grade_item_update($videotime, $grades=null) {
    global $CFG;

    require_once($CFG->libdir.'/gradelib.php');

    if (!videotime_has_pro()) {
        return null;
    }

    $params = [
        'itemname' => $videotime->name,
        'idnumber' => $videotime->cmidnumber,
        'gradetype' => GRADE_TYPE_NONE,
    ];

    if (!empty($videotime->viewpercentgrade)) {
        $params = [
            'gradetype' => GRADE_TYPE_VALUE,
            'grademax' => 100,
            'grademin' => 0,
        ] + $params;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/videotime', $videotime->course, 'mod', 'videotime', $videotime->id, 0, $grades, $params);
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $videotime
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone
 */
function videotime_update_grades($videotime, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!videotime_has_pro() || !$videotime->viewpercentgrade) {
        return null;
    }

    if ($grades = videotime_get_user_grades($videotime, $userid)) {
        videotime_grade_item_update($videotime, $grades);
    }
}

/**
 * Return grade for given user or all users.
 *
 * @param stdClass $videotime videotime instance
 * @param int $userid optional user id, 0 means all users
 * @return array|false array of grades, false if none.
 */
function videotime_get_user_grades($videotime, $userid = 0) {
    global $DB;

    if (!videotime_has_pro() || !$videotime->viewpercentgrade) {
        return false;
    }

    $cm = get_coursemodule_from_instance('videotime', $videotime->id);
    $videotime->cmidnumber = $cm->id;

    $params = ['cmid' => $cm->id];
    $where = 'WHERE module_id = :cmid';

    if ($userid > 0) {
        $params['userid'] = $userid;
        $where .= ' AND user_id = :userid';
    }

    return $DB->get_records_sql('SELECT
                                 user_id as userid,
                                 MAX(percent_watch)*100 as rawgrade
                                 FROM {' . \videotimeplugin_pro\session::TABLE . '}
                                 ' . $where . '
                                 GROUP BY user_id', $params);
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

    $moduleinstance->timecreated = time();

    $moduleinstance = videotime_process_video_description($moduleinstance);

    $moduleinstance->id = $DB->insert_record('videotime', $moduleinstance);

    videotime_grade_item_update($moduleinstance);

    // Plugins may need to use context now, so we need to make sure all needed info is already in db.
    $cmid = $moduleinstance->coursemodule;
    $DB->set_field('course_modules', 'instance', $moduleinstance->id, array('id' => $cmid));
    foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
        $classname = "\\videotimetab_$name\\tab";
        $classname::save_settings($moduleinstance);
    }

    if (videotime_has_repository()) {
        \videotimeplugin_repository\video::add_adhoc($moduleinstance->vimeo_url);
    }

    $completiontimeexpected = $moduleinstance->completionexpected ?? null;
    \core_completion\api::update_completion_date_event(
        $moduleinstance->coursemodule,
        'videotime',
        $moduleinstance->id,
        $completiontimeexpected
    );

    return $moduleinstance->id;
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

    videotime_grade_item_update($moduleinstance);

    if (videotime_has_repository()) {
        \videotimeplugin_repository\video::add_adhoc($moduleinstance->vimeo_url);
    }

    // Disable custom completion fields when changing completion from automatic to none or manual.
    if ($moduleinstance->completion != COMPLETION_TRACKING_AUTOMATIC) {
        $moduleinstance->completion_on_view_time = false;
        $moduleinstance->completion_on_percent = false;
        $moduleinstance->completion_on_finish = false;
    }

    $completiontimeexpected = $moduleinstance->completionexpected ?? null;
    \core_completion\api::update_completion_date_event(
        $moduleinstance->coursemodule,
        'videotime',
        $moduleinstance->id,
        $completiontimeexpected
    );

    foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
        $classname = "\\videotimetab_$name\\tab";
        $classname::save_settings($moduleinstance);
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

    $cm = get_coursemodule_from_instance('videotime', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'videotime', $id, null);

    foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
        $classname = "\\videotimetab_$name\\tab";
        $classname::delete_settings($id);
    }

    $DB->delete_records('videotime', array('id' => $id));

    return true;
}

/**
 * Process data submitted for videotime instance
 *
 * @param videotime_instance $moduleinstance
 * @return mixed
 */
function videotime_process_video_description($moduleinstance) {
    if (isset($moduleinstance->video_description['itemid'])) {
        $modcontext = context_module::instance($moduleinstance->coursemodule);
        $videodescription = $moduleinstance->video_description;
        $moduleinstance->video_description = file_save_draft_area_files($videodescription['itemid'], $modcontext->id,
            'mod_videotime', 'video_description', 0,
            array('subdirs' => true), $videodescription['text']);
        $moduleinstance->video_description_format = $videodescription['format'];
    }
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
function videotime_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    // Get forum details.
    $videotime = $DB->get_record('videotime', ['id' => $cm->instance], '*', MUST_EXIST);

    // Completion settings are pro features.
    if (!videotime_has_pro()) {
        return $type;
    }

    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

    if (!$videotime->completion_on_view_time && !$videotime->completion_on_finish && !$videotime->completion_on_percent) {
        // Completion options are not enabled so just return $type.
        return $type;
    }

    if (videotime_has_pro()) {
        $sessions = \videotimeplugin_pro\module_sessions::get($cm->id, $user->id);
    }

    // Check if watch time is required.
    if ($videotime->completion_on_view_time) {
        // If time was never set return false.
        if (!$videotime->completion_on_view_time_second) {
            return false;
        }
        // Check if total session time is over the required duration.
        if (videotime_has_pro() && $sessions->get_total_time() < $videotime->completion_on_view_time_second) {
            return false;
        }
    }

    if ($videotime->completion_on_percent) {
        // If percent value was never set return false.
        if (!$videotime->completion_on_percent_value) {
            return false;
        }
        // Check if watch percentage is met.
        if (videotime_has_pro() && ($sessions->get_percent() * 100) < $videotime->completion_on_percent_value) {
            return false;
        }
    }

    // Check if video completion is required.
    if (videotime_has_pro() && $videotime->completion_on_finish) {
        if (!$sessions->is_finished()) {
            return false;
        }
    }

    return true;
}

/**
 *  Update completion info
 *
 * @param int $cmid
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function videotime_update_completion($cmid) {
    global $DB, $CFG;

    require_once($CFG->libdir.'/completionlib.php');

    $cm = get_coursemodule_from_id('videotime', $cmid, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $moduleinstance = $DB->get_record('videotime', array('id' => $cm->instance), '*', MUST_EXIST);

    $completion = new \completion_info($course);
    // Update completion status only if any extra criteria is set on the activity.
    if ($completion->is_enabled($cm) && ($moduleinstance->completion_on_view_time || $moduleinstance->completion_on_finish ||
        $moduleinstance->completion_on_percent)
    ) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
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
 * @param  videotime_instance $videotime  Video Time object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 */
function videotime_view(videotime_instance $videotime, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $videotime->id
    );

    $event = \mod_videotime\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('videotime', $videotime->to_record());
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
 * Check if Video Time Repository is installed.
 *
 * @return bool
 */
function videotime_has_repository() {
    global $CFG;

    if (isset($CFG->disable_videotime_repository) && $CFG->disable_videotime_repository) {
        return false;
    }
    return array_key_exists('repository', core_component::get_plugin_list('videotimeplugin'));
}

/**
 * Check if system is Totara.
 *
 * @return bool
 */
function videotime_is_totara() {
    global $CFG;
    return file_exists("$CFG->dirroot/totara");
};

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

    if (videotime_has_pro() && $PAGE->cm && has_capability('mod/videotime:view_report', $PAGE->cm->context)) {
        $node = navigation_node::create(get_string('report'),
            new moodle_url('/mod/videotime/report.php', array('id' => $PAGE->cm->id)),
            navigation_node::TYPE_SETTING, null, 'mod_videotime_report',
            new pix_icon('t/grades', ''));
        $videtimenode->add_node($node, $beforekey);
    }

    // Give subplugins a chance to extend the settings navigation.
    foreach (core_component::get_plugin_list('videotimeplugin') as $plugin => $directory) {
        if (file_exists($directory . '/lib.php')) {
            require_once($directory . '/lib.php');
            $function = 'videotimeplugin_' . $plugin . '_extend_settings_navigation';
            if (function_exists($function)) {
                $function($settings, $videtimenode);
            }
        }
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
        $node->add(get_string('pluginname', 'videotime'), $url, navigation_node::TYPE_SETTING, null, null,
            new pix_icon('i/report', ''));
    }
}

/**
 * Sets dynamic information about a course module
 *
 * This function is called from cm_info when displaying the module
 * mod_folder can be displayed inline on course page and therefore have no course link
 *
 * @param cm_info $cm
 */
function videotime_cm_info_dynamic(cm_info $cm) {

    if (!videotime_has_pro()) {
        return;
    }

    $instance = videotime_instance::instance_by_id($cm->instance);

    if (in_array($instance->label_mode, [videotime_instance::LABEL_MODE, videotime_instance::PREVIEW_MODE])) {
        $cm->set_no_view_link();
    }
}

/**
 * Called when viewing course page.
 *
 * @param cm_info $cm Course module information
 */
function videotime_cm_info_view(cm_info $cm) {
    global $OUTPUT, $PAGE, $DB, $USER, $COURSE;

    try {

        $instance = videotime_instance::instance_by_id($cm->instance);

        // Ensure we are on the course view page. This was throwing an error when viewing the module
        // because OUTPUT was being used.
        if (!$PAGE->context || $PAGE->context->contextlevel != CONTEXT_COURSE) {
            if (!WS_SERVER && !AJAX_SCRIPT) {
                return;
            }
        }

        if (!videotime_has_pro() || $cm->deletioninprogress || !$cm->visible) {
            return;
        }

        $renderer = $PAGE->get_renderer('mod_videotime');

        if ($instance->label_mode == videotime_instance::LABEL_MODE) {
            $instance->set_embed(true);
            $content = $renderer->render($instance);
            $cm->set_extra_classes('label_mode');
        } else if ($instance->label_mode == videotime_instance::PREVIEW_MODE) {
            $preview = new \videotimeplugin_repository\output\video_preview($instance, $USER->id);
            $content = $renderer->render($preview);

            $columnclass = 'previewfull';
            if ($instance->columns == 2) {
                $columnclass = 'col-sm-6';
            } else if ($instance->columns == 3) {
                $columnclass = 'col-sm-4';
            } else if ($instance->columns == 4) {
                $columnclass = 'col-sm-3';
            }

            $cm->set_extra_classes('preview_mode ' . $columnclass);
        } else {
            // Normal mode, do not set any additional content.
            $content = null;
        }
    } catch (\Exception $e) {
        $content = $OUTPUT->notification(get_string('vimeo_video_not_found', 'videotime') . $e->getMessage());
    }

    if ($content) {
        $cm->set_content($content, true);
    }
}

/**
 * Add a get_coursemodule_info function in case any forum type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function videotime_get_coursemodule_info($coursemodule) {
    global $DB;

    $instance = videotime_instance::instance_by_id($coursemodule->instance);

    $result = new cached_cm_info();
    $result->name = $instance->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('forum', $instance, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        if ($instance->completion_hide_detail) {
            $result->customdata['customcompletionrules']['completion_hide_detail'] = $instance->completion_hide_detail;
        } else {
            if ($instance->completion_on_view_time) {
                $result->customdata['customcompletionrules']['completion_on_view_time_second']
                    = $instance->completion_on_view_time_second;
            }
            if ($instance->completion_on_percent) {
                $result->customdata['customcompletionrules']['completion_on_percent_value']
                    = $instance->completion_on_percent_value;
            }
            $result->customdata['customcompletionrules']['completion_on_finish'] = $instance->completion_on_finish;
        }
    }

    return $result;
}

/**
 * Get icon mapping for font-awesome.
 */
function mod_videotime_get_fontawesome_icon_map() {
    return [
        'mod_videotime:i/lock' => 'fa-lock',
    ];
}

/**
 * Get shortened version of description for display.
 *
 * @param string $description
 * @param int $maxlength
 * @return string
 */
function videotime_get_excerpt($description, $maxlength = 150) {
    if (strlen($description) > $maxlength) {
        $excerpt   = substr($description, 0, $maxlength - 3);
        $lastspace = strrpos($excerpt, ' ');
        $excerpt   = substr($excerpt, 0, $lastspace);
        $excerpt  .= '...';
    } else {
        $excerpt = $description;
    }

    return $excerpt;
}

/**
 * Check if Video Time module is displayed label-like or not.
 *
 * @param cm_info $mod
 * @return bool
 * @throws dml_exception
 */
function mod_videotime_treat_as_label(cm_info $mod) {
    global $DB;

    if ($mod->modname != 'videotime') {
        return false;
    }

    if ($instance = $DB->get_record('videotime', ['id' => $mod->instance])) {
        return $instance->label_mode == 1;
    }

    return false;
}

/**
 * Parse Vimeo link/URL and return video ID.
 *
 * @param string $link
 * @return mixed|null
 */
function mod_videotime_get_vimeo_id_from_link($link) {
    $videoid = null;
    if (preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $link, $outputarray)) {
        return $outputarray[5];
    }

    return null;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_videotime_core_calendar_provide_event_action(calendar_event $event,
                                                     \core_calendar\action_factory $factory,
                                                     int $userid = 0) {
    global $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['videotime'][$event->instance];

    if (!$cm->uservisible) {
        // The module is not visible to the user for any reason.
        return null;
    }

    $context = context_module::instance($cm->id);

    if (!has_capability('mod/videotime:view', $context, $userid)) {
        return null;
    }

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/videotime/view.php', ['id' => $cm->id]),
        1,
        true
    );
}
