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
 * @package     videotimeplugin_vimeo
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;

/**
 * Updates an instance of the videotimeplugin_vimeo in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_videotime_mod_form $mform The form.
 * @throws \dml_exception
 */
function videotimeplugin_vimeo_update_instance($moduleinstance, $mform = null) {
    global $DB;

    if (
        empty(get_config('videotimeplugin_vimeo', 'enabled'))
        || empty($moduleinstance->vimeo_url)
        || !mod_videotime_get_vimeo_id_from_link($moduleinstance->vimeo_url)
    ) {
        return;
    }

    $forced = videotime_forced_settings('videotimeplugin_vimeo');

    if ($record = $DB->get_record('videotimeplugin_vimeo', ['videotime' => $moduleinstance->id])) {
        $record = ['id' => $record->id, 'videotime' => $moduleinstance->id] + $forced + (array) $moduleinstance + (array) $record;
        $DB->update_record('videotimeplugin_vimeo', $record);
    } else {
        $record = ['id' => null, 'videotime' => $moduleinstance->id] + $forced
            + (array) $moduleinstance + (array) get_config('videotimeplugin_vimeo');
        $DB->insert_record('videotimeplugin_vimeo', $record);
    }
}

/**
 * Removes an instance of the mod_videotime from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function videotimeplugin_vimeo_delete_instance($id) {
    global $DB;

    $DB->delete_records('videotimeplugin_vimeo', ['videotime' => $id]);

    return true;
}

/**
 * Loads plugin settings into module record
 *
 * @param object $instance the module record.
 * @return object
 */
function videotimeplugin_vimeo_load_settings($instance) {
    global $DB;

    $instance = (object) $instance;
    if (
        empty(get_config('videotimeplugin_vimeo', 'enabled'))
        || !mod_videotime_get_vimeo_id_from_link($instance->vimeo_url)
    ) {
        return $instance;
    }

    if (get_config('videotimeplugin_vimeo', 'forced')) {
        $forced = array_intersect_key(
            (array) get_config('videotimeplugin_vimeo'),
            array_fill_keys(explode(',', get_config('videotimeplugin_vimeo', 'forced')), true)
        );
    } else {
        $forced = [];
    }

    $instance = (array) $instance;
    if (
        $record = $DB->get_record('videotimeplugin_vimeo', ['videotime' => $instance['id']])
    ) {
        unset($record->id);
        unset($record->videotime);
        return $forced + ((array) $record) + ((array) $instance);
    }

    return $forced + (array) get_config('videotimeplugin_vimeo') + (array) $instance;
}

/**
 * Loads plugin settings into module record
 *
 * @param object $instance the module record.
 * @param object $forcedsettings current forced settings
 * @return object
 */
function videotimeplugin_vimeo_forced_settings($instance, $forcedsettings) {
    global $DB;

    if (empty(get_config('videotimeplugin_vimeo', 'enabled')) || !get_config('videotimeplugin_vimeo', 'forced')) {
        return $forcedsettings;
    }

    $instance = (array) $instance;
    if (
        mod_videotime_get_vimeo_id_from_link($instance['vimeo_url'])
    ) {
        return array_fill_keys(explode(',', get_config('videotimeplugin_vimeo', 'forced')), true) + (array) $forcedsettings;
    }

    return $forcedsettings;
}

/**
 * Loads plugin player for instance
 *
 * @param object $instance the module record.
 * @return object|null
 */
function videotimeplugin_vimeo_embed_player($instance) {
    global $DB;

    if (
        empty(get_config('videotimeplugin_vimeo', 'enabled'))
        || !mod_videotime_get_vimeo_id_from_link($instance->vimeo_url)
    ) {
        return null;
    }

    return new \videotimeplugin_vimeo\video_embed($instance);
}
