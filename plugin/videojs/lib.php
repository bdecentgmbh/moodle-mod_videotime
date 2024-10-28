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
 * @package     videotimeplugin_videojs
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_videotime\videotime_instance;
use mod_videotime\embed_player;
use videotimeplugin_videojs\video_embed;

require_once("$CFG->libdir/resourcelib.php");

/**
 * Updates an instance of the videotimeplugin_videojs in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_videotime_mod_form $mform The form.
 * @throws \dml_exception
 */
function videotimeplugin_videojs_update_instance($moduleinstance, $mform = null) {
    global $DB;

    if (
        empty(get_config('videotimeplugin_videojs', 'enabled'))
    ) {
        return;
    }

    $context = context_module::instance($moduleinstance->coursemodule);

    if (
        !empty($moduleinstance->vimeo_url)
    ) {
        $fs = get_file_storage();
        foreach ($fs->get_area_files($context->id, 'videotimeplugin_videojs', 'mediafile') as $file) {
            $file->delete();
        }
        if (
            mod_videotime_get_vimeo_id_from_link($moduleinstance->vimeo_url)
        ) {
            return;
        }
    }

    if ($record = $DB->get_record('videotimeplugin_videojs', ['videotime' => $moduleinstance->id])) {
        $record = ['id' => $record->id, 'videotime' => $moduleinstance->id] + (array) $moduleinstance + (array) $record;
        $DB->update_record('videotimeplugin_videojs', $record);
    } else {
        $record = ['id' => null, 'videotime' => $moduleinstance->id]
            + (array) $moduleinstance + (array) get_config('videotimeplugin_videojs');
        $record['id'] = $DB->insert_record('videotimeplugin_videojs', $record);
    }

    if (!empty($mform) && $data = $mform->get_data()) {
        if (get_class($mform) == 'videotimeplugin_videojs\form\options') {
            file_save_draft_area_files(
                $data->poster,
                $context->id,
                'videotimeplugin_videojs',
                'poster',
                0,
                ['subdirs' => 0, 'maxfiles' => 1]
            );
        } else if (!empty($data->mediafile)) {
            file_save_draft_area_files(
                $data->mediafile,
                $context->id,
                'videotimeplugin_videojs',
                'mediafile',
                0,
                ['subdirs' => 0, 'maxfiles' => 1]
            );
        }
    }
}

/**
 * Removes an instance of the mod_videotime from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function videotimeplugin_videojs_delete_instance($id) {
    global $DB;

    $DB->delete_records('videotimeplugin_videojs', ['videotime' => $id]);

    return true;
}

/**
 * Loads plugin settings into module record
 *
 * @param object $instance the module record.
 * @return object
 */
function videotimeplugin_videojs_load_settings($instance) {
    global $DB, $USER;

    $instance = (object) $instance;
    if (
        empty(get_config('videotimeplugin_videojs', 'enabled'))
        || mod_videotime_get_vimeo_id_from_link($instance->vimeo_url)
    ) {
        return $instance;
    }

    $instance = (array) $instance;
    if (get_config('videotimeplugin_videojs', 'forced')) {
        $forced = array_intersect_key(
            (array) get_config('videotimeplugin_videojs'),
            array_fill_keys(explode(',', get_config('videotimeplugin_videojs', 'forced')), true)
        );
    } else {
        $forced = [];
    }

    if (
        !mod_videotime_get_vimeo_id_from_link($instance['vimeo_url'])
        && $record = $DB->get_record('videotimeplugin_videojs', ['videotime' => $instance['id']])
    ) {
        unset($record->id);
        unset($record->videotime);

        if (empty($instance['vimeo_url'])) {
            $cm = get_coursemodule_from_instance('videotime', $instance['id']);
            $context = context_module::instance($cm->id);
            $fs = get_file_storage();
            foreach ($fs->get_area_files($context->id, 'videotimeplugin_videojs', 'mediafile') as $file) {
                if (!$file->is_directory()) {
                    $instance['vimeo_url'] = moodle_url::make_pluginfile_url(
                        $context->id,
                        'videotimeplugin_videojs',
                        'mediafile',
                        0,
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out(false);
                }
            }
        }
        if (!empty($instance['vimeo_url'])) {
            if (videotimeplugin_videojs_youtube($instance['vimeo_url'])) {
                $instance['type'] = 'video/youtube';
            } else {
                $instance['type'] = resourcelib_guess_url_mimetype($instance['vimeo_url']);
            }
        }

        return $forced + (array) $record + (array) $instance;
    }

    return  (array) get_config('videotimeplugin_videojs') + (array) $instance;
}

/**
 * Check whether url is valid youtube resource
 *
 * @param string $url URL to check
 * @return bool Whether it matches youtube format
 */
function videotimeplugin_videojs_youtube($url) {
    // Regex for standard youtube link.
    $link = '(youtube(-nocookie)?\.com/(?:watch\?v=|v/))';
    // Regex for shortened youtube link.
    $shortlink = '((youtu|y2u)\.be/)';

    // Initial part of link.
    $start = '~^https?://(www\.)?(' . $link . '|' . $shortlink . ')';
    // Middle bit: Video key value.
    $middle = '([a-z0-9\-_]+)';
    $regex = $start . $middle . core_media_player_external::END_LINK_REGEX_PART;

    return !empty(preg_match($regex, $url));
}

/**
 * Loads plugin settings into module record
 *
 * @param object $instance the module record.
 * @param array $forcedsettings current forced settings array
 * @return array
 */
function videotimeplugin_videojs_forced_settings($instance, $forcedsettings) {
    global $DB;

    if (empty(get_config('videotimeplugin_videojs', 'enabled')) || !get_config('videotimeplugin_videojs', 'forced')) {
        return $forcedsettings;
    }

    $instance = (array) $instance;
    if (
        !mod_videotime_get_vimeo_id_from_link($instance['vimeo_url'])
    ) {
        return array_fill_keys(explode(',', get_config('videotimeplugin_videojs', 'forced')), true) + (array) $forcedsettings;
    }

    return $forcedsettings;
}

/**
 * Loads plugin player for instance
 *
 * @param object $instance the module record.
 * @return object|null
 */
function videotimeplugin_videojs_embed_player($instance) {
    global $DB;

    if (
        empty(get_config('videotimeplugin_videojs', 'enabled'))
        || empty($instance->vimeo_url)
        || mod_videotime_get_vimeo_id_from_link($instance->vimeo_url)
    ) {
        return null;
    }

    return new video_embed($instance);
}

/**
 * Add additional fields to form
 *
 * @param moodleform $mform Setting form to modify
 * @param string $formclass Class nam of the form
 */
function videotimeplugin_videojs_add_form_fields($mform, $formclass) {
    global $COURSE, $OUTPUT, $PAGE;

    if ($formclass === 'mod_videotime_mod_form') {
        $mform->insertElementBefore(
            $mform->createElement('filemanager', 'mediafile', get_string('mediafile', 'videotimeplugin_videojs'), null, [
                'subdirs' => 0,
                'maxfiles' => 1,
            ]),
            'name'
        );
        $mform->addHelpButton('mediafile', 'mediafile', 'videotimeplugin_videojs');
    }
}

/**
 * Prepares the form before data are set
 *
 * @param  array $defaultvalues
 * @param  int $instance
 */
function videotimeplugin_videojs_data_preprocessing(array &$defaultvalues, int $instance) {
    global $DB;

    if (!empty($instance)) {
        $draftitemid = file_get_submitted_draft_itemid('mediafile');
        $cm = get_coursemodule_from_instance('videotime', $instance);
        $context = context_module::instance($cm->id);
        file_prepare_draft_area(
            $draftitemid,
            $context->id,
            'videotimeplugin_videojs',
            'mediafile',
            0
        );
        $defaultvalues['mediafile'] = $draftitemid;

        // Do not load a url if we have a file.
        $fs = get_file_storage();
        if (count($fs->get_area_files($context->id, 'videotimeplugin_videojs', 'mediafile', 0)) > 1) {
            $defaultvalues['vimeo_url'] = '';
        }

        $draftitemid = file_get_submitted_draft_itemid('poster');
        file_prepare_draft_area(
            $draftitemid,
            $context->id,
            'videotimeplugin_videojs',
            'poster',
            0
        );
        $defaultvalues['poster'] = $draftitemid;
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
function videotimeplugin_videojs_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea == 'audioimage') {
        // Extract the filename / filepath from the $args array.
        $filename = array_pop($args);
        if (!$args) {
            $filepath = '/';
        } else {
            $filepath = '/' . implode('/', $args) . '/';
        }

        // Retrieve the file from the Files API.
        $itemid = 0;
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'videotimeplugin_videojs', $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false; // The file does not exist.
        }

        send_stored_file($file, null, 0, $forcedownload, $options);
    }

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if (
        in_array($filearea, [
        'mediafile', 'poster',
        ])
    ) {
        $relativepath = implode('/', $args);

        $fullpath = "/$context->id/videotimeplugin_videojs/$filearea/$relativepath";

        $fs = get_file_storage();
        if ((!$file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
            return false;
        }

        send_stored_file($file, null, 0, $forcedownload, $options);
    }
}

/**
 * Return file areas for backup
 *
 * @return array List of file areas
 */
function videotimeplugin_videojs_config_file_areas() {
    return [
        'mediafile',
        'poster',
    ];
}

/**
 * Validates the form input
 *
 * @param array $data submitted data
 * @param array $files submitted files
 * @return array eventual errors indexed by the field name
 */
function videotimeplugin_videojs_validation($data, $files) {
    global $USER;

    $acceptedtypes = [
        'audio/flac',
        'audio/mp3',
        'audio/ogg',
        'audio/wav',
        'video/mp4',
        'video/mpeg',
        'video/ogg',
        'video/quicktime',
        'video/wav',
        'video/webm',
    ];
    if (
        !empty($data['livefeed'])
        || in_array(resourcelib_guess_url_mimetype($data['vimeo_url']), $acceptedtypes)
        || mod_videotime_get_vimeo_id_from_link($data['vimeo_url'])
        || (resourcelib_get_extension($data['vimeo_url']) == 'm3u8')
        || videotimeplugin_videojs_youtube($data['vimeo_url'])
    ) {
        $fs = get_file_storage();
        if (count($fs->get_area_files(\context_user::instance($USER->id)->id, 'user', 'draft', $data['mediafile'])) < 2) {
            return [];
        }
        return ['mediafile' => get_string('fileorurl', 'videotimeplugin_videojs')];
    }
    foreach ($files as $file) {
        if (
            !$file->is_directory()
            && ($file->get_itemid() == $data['mediafile'])
        ) {
            if (
                in_array($file->get_mimetype(), $acceptedtypes)
                || (resourcelib_get_extension($file->get_filename()) == 'm3u8')
            ) {
                return [];
            }
            return ['mediafile' => get_string('invalidmediafile', 'videotimeplugin_videojs')];
        }
    }
    return ['vimeo_url' => get_string('required')];
}
