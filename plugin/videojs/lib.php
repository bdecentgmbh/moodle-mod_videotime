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

use mod_videotime\videotime_instance;
use mod_videotime\embed_player;
use videotimeplugin_videojs\video_embed;

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
        mod_videotime_get_vimeo_id_from_link($moduleinstance->vimeo_url)
        || empty(get_config('videotimeplugin_videojs', 'enabled'))
    ) {
        return;
    }

    if ($record = $DB->get_record('videotimeplugin_videojs', ['videotime' => $moduleinstance->id])) {
        $record = ['id' => $record->id, 'videotime' => $moduleinstance->id] + (array) $moduleinstance + (array) $record;
        $DB->update_record('videotimeplugin_videojs', $record);
    } else {
        $record = ['id' => null, 'videotime' => $moduleinstance->id] + (array) $moduleinstance;
        $DB->insert_record('videotimeplugin_videojs', $record);
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

    $DB->delete_records('videotimeplugin_videojs', array('videotime' => $id));

    return true;
}

/**
 * Loads plugin settings into module record
 *
 * @param object $instance the module record.
 * @return object
 */
function videotimeplugin_videojs_load_settings($instance) {
    global $DB;

    $instance = (object) $instance;
    if (
        empty(get_config('videotimeplugin_videojs', 'enabled'))
        || mod_videotime_get_vimeo_id_from_link($instance->vimeo_url)
    ) {
        return $instance;
    }

    $instance = (array) $instance;
    if (
        !mod_videotime_get_vimeo_id_from_link($instance['vimeo_url'])
        && $record = $DB->get_record('videotimeplugin_videojs', array('videotime' => $instance['id']))
    ) {
        unset($record->id);
        unset($record->videotime);
        return ((array) $record) + ((array) $instance);
    }

    return (array) $instance + (array) get_config('videotimeplugin_videojs');
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
        || mod_videotime_get_vimeo_id_from_link($instance->vimeo_url)
    ) {
        return null;
    }

    return new video_embed($instance);
}
