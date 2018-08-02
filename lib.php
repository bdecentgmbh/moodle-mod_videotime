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
 * @package     mod_vimeo
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
function vimeo_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:      return true;
        case FEATURE_BACKUP_MOODLE2: return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_vimeo into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_vimeo_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function vimeo_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $moduleinstance = vimeo_process_video_description($moduleinstance);

    $id = $DB->insert_record('vimeo', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_vimeo in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_vimeo_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function vimeo_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    $moduleinstance = vimeo_process_video_description($moduleinstance);

    return $DB->update_record('vimeo', $moduleinstance);
}

/**
 * Removes an instance of the mod_vimeo from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function vimeo_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('vimeo', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('vimeo', array('id' => $id));

    return true;
}

/**
 * @param $moduleinstance
 * @return mixed
 */
function vimeo_process_video_description($moduleinstance) {
    $modcontext = context_module::instance($moduleinstance->coursemodule);
    $video_description = $moduleinstance->video_description;
    $moduleinstance->video_description = file_save_draft_area_files($video_description['itemid'], $modcontext->id,
        'mod_vimeo', 'video_description', 0,
        array('subdirs'=>true), $video_description['text']);
    $moduleinstance->video_description_format = $video_description['format'];
    return $moduleinstance;
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
function vimeo_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea == 'video_description' || $filearea == 'intro') {

        $relativepath = implode('/', $args);

        $fullpath = "/$context->id/mod_vimeo/$filearea/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, null, 0, $forcedownload, $options);
    }
}