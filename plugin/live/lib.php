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
 * @package     videotimeplugin_live
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_videotime\videotime_instance;
use mod_videotime\embed_player;
use videotimeplugin_live\video_embed;

require_once("$CFG->libdir/resourcelib.php");

/**
 * Updates an instance of the videotimeplugin_live in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_videotime_mod_form $mform The form.
 * @throws \dml_exception
 */
function videotimeplugin_live_update_instance($moduleinstance, $mform = null) {
    global $DB;

    if (
        empty(get_config('videotimeplugin_live', 'enabled'))
        || empty(get_config('block_deft', 'enableupdating'))
    ) {
        return;
    }

    if (!empty($moduleinstance->livefeed)) {
        if ($record = $DB->get_record('videotimeplugin_live', ['videotime' => $moduleinstance->id])) {
            $record = ['id' => $record->id, 'videotime' => $moduleinstance->id] + (array) $moduleinstance + (array) $record;
            $DB->update_record('videotimeplugin_live', $record);
        } else {
            $record = ['id' => null, 'videotime' => $moduleinstance->id]
                + (array) $moduleinstance + (array) get_config('videotimeplugin_live');
            $record['id'] = $DB->insert_record('videotimeplugin_live', $record);
        }
    } else {
        $DB->delete_records('videotimeplugin_live', ['videotime' => $moduleinstance->id]);
    }
}

/**
 * Removes an instance of the mod_videotime from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function videotimeplugin_live_delete_instance($id) {
    global $DB;

    $DB->delete_records('videotimeplugin_live', ['videotime' => $id]);
    if (class_exists('\\block_deft\\janus_room')) {
        \block_deft\janus_room::remove('videotimeplugin_live', $id);
    }

    return true;
}

/**
 * Loads plugin settings into module record
 *
 * @param object $instance the module record.
 * @return object
 */
function videotimeplugin_live_load_settings($instance) {
    global $DB, $USER;

    $instance = (object) $instance;
    if (
        empty(get_config('videotimeplugin_live', 'enabled'))
        || mod_videotime_get_vimeo_id_from_link($instance->vimeo_url)
    ) {
        return $instance;
    }

    $instance = (array) $instance;
    if (
        $record = $DB->get_record('videotimeplugin_live', ['videotime' => $instance['id']])
    ) {
        unset($record->id);
        unset($record->videotime);

        return ((array) $record) + ((array) $instance) + ['livefeed' => 1];
    }

    return (array) $instance + (array) get_config('videotimeplugin_live');
}

/**
 * Loads plugin settings into module record
 *
 * @param object $instance the module record.
 * @param array $forcedsettings current forced settings array
 * @return array
 */
function videotimeplugin_live_forced_settings($instance, $forcedsettings) {
    global $DB;

    if (empty(get_config('videotimeplugin_live', 'enabled')) || !get_config('videotimeplugin_live', 'forced')) {
        return $forcedsettings;
    }

    $instance = (array) $instance;
    if (
        !mod_videotime_get_vimeo_id_from_link($instance['vimeo_url'])
    ) {
        return array_fill_keys(explode(',', get_config('videotimeplugin_live', 'forced')), true) + (array) $forcedsettings;
    }

    return $forcedsettings;
}

/**
 * Loads plugin player for instance
 *
 * @param object $instance the module record.
 * @return object|null
 */
function videotimeplugin_live_embed_player($instance) {
    global $DB;

    if (
        empty(get_config('videotimeplugin_live', 'enabled'))
        || empty(get_config('block_deft', 'enableupdating'))
        || !$DB->get_record('videotimeplugin_live', ['videotime' => $instance->id])
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
function videotimeplugin_live_add_form_fields($mform, $formclass) {
    global $COURSE, $DB, $OUTPUT, $PAGE;

    if (
        !empty(get_config('videotimeplugin_live', 'enabled'))
        && !empty(get_config('block_deft', 'enableupdating'))
        && $formclass === 'mod_videotime_mod_form'
    ) {
        $mform->insertElementBefore(
            $mform->createElement('advcheckbox', 'livefeed', get_string('livefeed', 'videotimeplugin_live')),
            'name'
        );
        $instance = $mform->getElementValue('instance');
        $mform->addHelpButton('livefeed', 'livefeed', 'videotimeplugin_live');
        $mform->disabledIf('vimeo_url', 'livefeed', 'checked');
        $mform->setDefault('livefeed', 0);
    }
}

/**
 * Prepares the form before data are set
 *
 * @param  array $defaultvalues
 * @param  int $instance
 */
function videotimeplugin_live_data_preprocessing(array &$defaultvalues, int $instance) {
    global $DB;

    if (empty($instance)) {
        $settings = (array) get_config('videotimeplugin_live');
    } else {
        $settings = (array) $DB->get_record('videotimeplugin_live', ['videotime' => $instance]);
        $defaultvalues['livefeed'] = !empty($settings['id']);
        unset($settings['id']);
        unset($settings['videotime']);
    }

    foreach ($settings as $key => $value) {
        $defaultvalues[$key] = $value;
    }
}

/**
 * Serves the poster image file setting.
 *
 * @param   stdClass $course course object
 * @param   stdClass $cm course module object
 * @param   stdClass $context context object
 * @param   string $filearea file area
 * @param   array $args extra arguments
 * @param   bool $forcedownload whether or not force download
 * @param   array $options additional options affecting the file serving
 * @return  bool false|void
 */
function videotimeplugin_live_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    if ($filearea !== 'poster') {
        return false;
    }

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
    $file = $fs->get_file($context->id, 'videotimeplugin_live', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Add the control block to default region
 *
 * @param   stdClass    $instance   Video Time instance
 * @param   stdClass    $cm         The course module
 */
function videotimeplugin_live_setup_page($instance, $cm) {
    global $OUTPUT, $PAGE, $USER;

    $context = context_module::instance($cm->id);
    if (empty($instance->livefeed) || !has_capability('block/deft:moderate', $context)) {
        return;
    }

    $bc = new block_contents();
    $bc->title = get_string('sharedvideo', 'videotimeplugin_live');
    $bc->attributes['class'] = 'block block_book_toc';
    if (get_config('block_deft', 'enablevideo')) {
        $bc->content = $OUTPUT->render_from_template('videotimeplugin_live/controls', [
            'contextid' => $context->id,
            'instance' => $instance,
            'types' => [
                ['type' => 'camera'],
                ['type' => 'display'],
            ],
        ]);
    } else {
        $bc->content = get_string('enabledeftvideo', 'videotimeplugin_live');
    }

    $defaultregion = $PAGE->blocks->get_default_region();
    $PAGE->blocks->add_fake_block($bc, $defaultregion);
}
