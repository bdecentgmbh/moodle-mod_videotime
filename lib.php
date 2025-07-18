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

defined('MOODLE_INTERNAL') || die();

define('VIDEOTIME_EVENT_TYPE_OPEN', 'open');
define('VIDEOTIME_EVENT_TYPE_CLOSE', 'close');

require_once($CFG->dirroot . '/lib/completionlib.php');

use mod_videotime\videotime_instance;

/**
 * Checks if Videotime supports a specific feature.
 *
 * Return if the plugin supports $feature.
 * http://localhost/moodle35/course/mod.php?sesskey=nzkiyHyS2D&sr=0&update=2
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function videotime_supports($feature) {
    if (defined('FEATURE_MOD_PURPOSE') && $feature == FEATURE_MOD_PURPOSE) {
        return MOD_PURPOSE_CONTENT;
    }
    switch ($feature) {
        case FEATURE_COMPLETION_HAS_RULES:
            return videotime_has_pro();
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return videotime_has_pro();
        case FEATURE_GROUPS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
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
function videotime_grade_item_update($videotime, $grades = null) {
    global $CFG;

    require_once($CFG->libdir . '/gradelib.php');

    if (!videotime_has_pro()) {
        return null;
    }

    $params = [
        'itemname' => $videotime->name,
        'idnumber' => $videotime->idnumber ?? null,
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
function videotime_update_grades($videotime, $userid = 0, $nullifnone = true) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

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

    $moduleinstance = (array) $moduleinstance + [
        'height' => 0,
        'maxheight' => 0,
        'maxwidth' => 0,
        'width' => 0,
        'vimeo_url' => '',
    ];

    $moduleinstance = (object) $moduleinstance;

    $moduleinstance->id = $DB->insert_record('videotime', $moduleinstance);

    foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
        component_callback("videotimeplugin_$name", 'update_instance', [$moduleinstance, $mform]);
    }

    videotime_grade_item_update($moduleinstance);

    // Plugins may need to use context now, so we need to make sure all needed info is already in db.
    $cmid = $moduleinstance->coursemodule;
    $DB->set_field('course_modules', 'instance', $moduleinstance->id, ['id' => $cmid]);
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

    $cm = get_coursemodule_from_id('videotime', $moduleinstance->coursemodule);

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $cm->instance;

    $moduleinstance = videotime_process_video_description($moduleinstance);

    videotime_grade_item_update($moduleinstance);

    if (videotime_has_repository() && !empty($moduleinstance->vimeo_url)) {
        \videotimeplugin_repository\video::add_adhoc($moduleinstance->vimeo_url);
    }

    // Disable custom completion fields when changing completion from automatic to none or manual.
    if ($cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
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

    foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
        component_callback("videotimeplugin_$name", 'update_instance', [$moduleinstance, $mform]);
    }

    foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
        $classname = "\\videotimetab_$name\\tab";
        $classname::save_settings($moduleinstance);
    }

    if (!empty($mform) && !empty($mform->get_data()->livefeed)) {
        $moduleinstance->vimeo_url = '';
    }

    $moduleinstance->viewpercentgrade = $moduleinstance->viewpercentgrade ?? 0;

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

    $exists = $DB->get_record('videotime', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $cm = get_coursemodule_from_instance('videotime', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'videotime', $id, null);

    foreach (array_keys(core_component::get_plugin_list('videotimetab')) as $name) {
        $classname = "\\videotimetab_$name\\tab";
        $classname::delete_settings($id);
    }

    foreach (array_keys(core_component::get_plugin_list('videotimeplugin')) as $name) {
        component_callback("videotimeplugin_$name", 'delete_instance', [$id]);
    }

    $DB->delete_records('videotime', ['id' => $id]);

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
        $moduleinstance->video_description = file_save_draft_area_files(
            $videodescription['itemid'],
            $modcontext->id,
            'mod_videotime',
            'video_description',
            0,
            ['subdirs' => true],
            $videodescription['text']
        );
        $moduleinstance->video_description_format = $videodescription['format'];
    }
    return $moduleinstance;
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

    require_once($CFG->libdir . '/completionlib.php');

    $cm = get_coursemodule_from_id('videotime', $cmid, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $moduleinstance = $DB->get_record('videotime', ['id' => $cm->instance], '*', MUST_EXIST);

    $completion = new \completion_info($course);
    // Update completion status only if any extra criteria is set on the activity.
    if (
        $completion->is_enabled($cm) && ($moduleinstance->completion_on_view_time || $moduleinstance->completion_on_finish ||
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
function videotime_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea == 'video_description' || $filearea == 'intro') {
        $relativepath = implode('/', $args);

        $fullpath = "/$context->id/mod_videotime/$filearea/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) || $file->is_directory()) {
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
    global $USER;

    // Trigger course_module_viewed event.
    $params = [
        'context' => $context,
        'objectid' => $videotime->id,
    ];

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
    if (!get_config('videotimeplugin_pro', 'enabled')) {
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
    if (!get_config('videotimeplugin_repository', 'enabled') || !videotime_has_pro()) {
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
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (
        $PAGE->cm &&
        has_capability('moodle/course:manageactivities', $PAGE->cm->context)
    ) {
        $node = navigation_node::create(
            get_string('embed_options', 'mod_videotime'),
            new moodle_url('/mod/videotime/options.php', ['id' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mod_videotime_options',
            new pix_icon('t/play', '')
        );
        $videtimenode->add_node($node, $beforekey);
    }
    if (videotime_has_pro() && $PAGE->cm && has_capability('mod/videotime:view_report', $PAGE->cm->context)) {
        $node = navigation_node::create(
            get_string('report'),
            new moodle_url('/mod/videotime/report.php', ['id' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mod_videotime_report',
            new pix_icon('t/grades', '')
        );
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
        $node->add(
            get_string('pluginname', 'videotime'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/report', '')
        );
    }
}

/**
 * Called when viewing course page.
 *
 * @param cm_info $cm Course module information
 */
function videotime_cm_info_view(cm_info $cm) {
    global $OUTPUT, $PAGE, $USER;

    try {
        // Ensure we are on the course view page. This was throwing an error when viewing the module
        // because OUTPUT was being used.
        if (!$PAGE->context || $PAGE->context->contextlevel != CONTEXT_COURSE) {
            if (!WS_SERVER && !AJAX_SCRIPT) {
                return;
            }
        }

        if ($cm->customdata['labelmode'] == videotime_instance::NORMAL_MODE) {
            return;
        }

        if (!videotime_has_pro() || $cm->deletioninprogress || !$cm->visible) {
            return;
        }

        $renderer = $PAGE->get_renderer('mod_videotime');

        $instance = videotime_instance::instance_by_id($cm->instance);

        if ($cm->customdata['labelmode'] == videotime_instance::LABEL_MODE) {
            $instance->set_embed(true);
            $content = $renderer->render($instance);
            $cm->set_extra_classes('label_mode');
        } else if ($cm->customdata['labelmode'] == videotime_instance::PREVIEW_MODE) {
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
 * Add a get_coursemodule_info function to add description and completion information
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

    if (empty($coursemodule->instance)) {
        return false;
    }
    $instance = videotime_instance::instance_by_id($coursemodule->instance);

    $result = new cached_cm_info();
    $result->name = $instance->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('videotime', $instance, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        if ($instance->completion_hide_detail) {
            $result->customdata['customcompletionrules']['completion_hide_detail'] = $instance->completion_hide_detail;
        } else {
            if ($instance->completion_on_view_time) {
                $result->customdata['customcompletionrules']['completion_on_view_time']
                    = $instance->completion_on_view_time_second;
            }
            if ($instance->completion_on_percent) {
                $result->customdata['customcompletionrules']['completion_on_percent'] = $instance->completion_on_percent_value;
            }
            $result->customdata['customcompletionrules']['completion_on_finish'] = $instance->completion_on_finish;
        }
    }

    if ($instance->timeclose) {
        $result->customdata['timeclose'] = $instance->timeclose;
    }
    if ($instance->timeopen) {
        $result->customdata['timeopen'] = $instance->timeopen;
    }

    $result->customdata['labelmode'] = $instance->label_mode;

    return $result;
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
    return ($mod->modname != 'videotime') && ($mod->customdata['labelmode'] == 1);
}

/**
 * Parse Vimeo link/URL and return video ID.
 *
 * @param string $link
 * @return mixed|null
 */
function mod_videotime_get_vimeo_id_from_link($link) {
    $videoid = null;
    if (
        preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $link, $outputarray)
        && get_config('videotimeplugin_vimeo', 'enabled')
    ) {
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
function mod_videotime_core_calendar_provide_event_action(
    calendar_event $event,
    \core_calendar\action_factory $factory,
    int $userid = 0
) {
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

/**
 * Return array of the settings that are forced
 *
 * @param string $component component to us for plugins
 * @return array Settings that are forced
 */
function videotime_forced_settings($component = 'videotime') {

    $config = (array) get_config($component);
    $forced = array_intersect($config, explode(',', $config['forced'] ?? ''));

    return $forced;
}

/**
 * Callback to fetch the activity event type lang string.
 *
 * @param string $eventtype The event type.
 * @return lang_string The event type lang string.
 */
function mod_videotime_core_calendar_get_event_action_string(string $eventtype): string {
    $modulename = get_string('modulename', 'videotime');

    switch ($eventtype) {
        case VIDEOTIME_EVENT_TYPE_OPEN:
            $identifier = 'calendarstart';
            break;
        case VIDEOTIME_EVENT_TYPE_CLOSE:
            $identifier = 'calendarend';
            break;
        default:
            return get_string('requiresaction', 'calendar', $modulename);
    }

    return get_string($identifier, 'videotime', $modulename);
}

/**
 * Sets dynamic information about a course module
 *
 * This function is called from cm_info when displaying the module
 *
 * @param cm_info $cm
 */
function videotime_cm_info_dynamic(cm_info $cm) {
    global $PAGE, $USER;

    if (
        defined('BEHAT_SITE_RUNNING')
        || !$PAGE->has_set_url()
        || ($PAGE->pagetype == 'course-modedit')
        || $PAGE->user_is_editing()
    ) {
        return;
    }

    if (
        ($cm->customdata['labelmode'] == videotime_instance::LABEL_MODE)
        || ($cm->customdata['labelmode'] == videotime_instance::PREVIEW_MODE)
    ) {
        $cm->set_no_view_link();
    }
}

/**
 * Register the ability to handle drag and drop file uploads
 *
 * @return array containing details of the files / types the mod can handle
 */
function videotime_dndupload_register(): array {
    global $CFG;

    if ($CFG->branch < 404) {
        return [];
    }

    $hook = new \mod_videotime\hook\dndupload_register();
    \core\di::get(\core\hook\manager::class)->dispatch($hook);

    return ['files' => array_map(function($extension) {
        return [
            'extension' => $extension,
            'message' => get_string('dnduploadvideotime', 'videotime'),
        ];
    }, $hook->get_extensions())];
}

/**
 * Handle a file that has been uploaded
 *
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function videotime_dndupload_handle($uploadinfo): int {
    global $CFG;

    $hook = new \mod_videotime\hook\dndupload_handle($uploadinfo);
    \core\di::get(\core\hook\manager::class)->dispatch($hook);
    return $hook->get_instanceid();
}
