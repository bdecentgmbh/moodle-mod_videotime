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
 * Upgrade script for the Video Time.
 *
 * @package     mod_videotime
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_videotime_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018080205) {

        // Define field completion_on_view_time to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_on_view_time', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0',
            'timemodified');

        // Conditionally launch add field completion_on_view_time.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field completion_on_view_time_second to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_on_view_time_second', XMLDB_TYPE_INTEGER, '10', null, null, null, null,
            'completion_on_view_time');

        // Conditionally launch add field completion_on_view_time_second.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field completion_on_finish to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_on_finish', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0',
            'completion_on_view_time_second');

        // Conditionally launch add field completion_on_finish.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2018080205, 'videotime');
    }

    if ($oldversion < 2018080213) {

        // Define field completion_on_percent to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_on_percent', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0',
            'completion_on_finish');

        // Conditionally launch add field completion_on_percent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field completion_on_percent_value to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_on_percent_value', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0',
            'completion_on_percent');

        // Conditionally launch add field completion_on_percent_value.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2018080213, 'videotime');
    }

    if ($oldversion < 2018080215) {

        // Define field autoplay to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('autoplay', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0',
            'completion_on_percent_value');

        // Conditionally launch add field autoplay.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field byline to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('byline', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'autoplay');

        // Conditionally launch add field byline.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field color to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('color', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, '00adef', 'byline');

        // Conditionally launch add field color.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field height to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('height', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'color');

        // Conditionally launch add field height.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field maxheight to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('maxheight', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'height');

        // Conditionally launch add field maxheight.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field maxwidth to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('maxwidth', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'maxheight');

        // Conditionally launch add field maxwidth.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field muted to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('muted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'maxwidth');

        // Conditionally launch add field muted.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field playsinline to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('playsinline', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'muted');

        // Conditionally launch add field playsinline.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field portrait to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('portrait', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'playsinline');

        // Conditionally launch add field portrait.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field speed to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('speed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'portrait');

        // Conditionally launch add field speed.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field title to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('title', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'speed');

        // Conditionally launch add field title.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field transparent to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('transparent', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'title');

        // Conditionally launch add field transparent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field width to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('width', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'transparent');

        // Conditionally launch add field width.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2018080215, 'videotime');
    }

    if ($oldversion < 2018080218) {

        // Define field responsive to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('responsive', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'width');

        // Conditionally launch add field responsive.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2018080218, 'videotime');
    }

    if ($oldversion < 2019031901) {

        // Define field label_mode to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('label_mode', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'responsive');

        // Conditionally launch add field label_mode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019031901, 'videotime');
    }

    if ($oldversion < 2019071200) {

        // Define field viewpercentgrade to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('viewpercentgrade', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'label_mode');

        // Conditionally launch add field viewpercentgrade.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019071200, 'videotime');
    }

    if ($oldversion < 2019082800) {

        // Define field next_activity_button to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('next_activity_button', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'viewpercentgrade');

        // Conditionally launch add field next_activity_button.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field next_activity_id to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('next_activity_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'next_activity_button');

        // Conditionally launch add field next_activity_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019082800, 'videotime');
    }

    if ($oldversion < 2019082801) {

        // Define field resume_playback to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('resume_playback', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'next_activity_id');

        // Conditionally launch add field resume_playback.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019082801, 'videotime');
    }

    if ($oldversion < 2019082901) {

        // Define field next_activity_auto to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('next_activity_auto', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'next_activity_id');

        // Conditionally launch add field next_activity_auto.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019082901, 'videotime');
    }

    if ($oldversion < 2019100201) {

        // Define field preview_image to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('preview_image', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'resume_playback');

        // Conditionally launch add field preview_image.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019100201, 'videotime');
    }

    if ($oldversion < 2019101000) {

        // Define field show_description to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_description', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'preview_image');

        // Conditionally launch add field show_description.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field show_title to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_title', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'show_description');

        // Conditionally launch add field show_title.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field show_tags to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_tags', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'show_title');

        // Conditionally launch add field show_tags.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field show_duration to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_duration', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'show_tags');

        // Conditionally launch add field show_duration.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field show_viewed_duration to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_viewed_duration', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'show_duration');

        // Conditionally launch add field show_viewed_duration.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019101000, 'videotime');
    }

    if ($oldversion < 2019101100) {

        // Rename field preview_image on table videotime to preview_picture.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('preview_image', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'resume_playback');

        // Launch rename field preview_image.
        $dbman->rename_field($table, $field, 'preview_picture');

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019101100, 'videotime');
    }

    if ($oldversion < 2019101502) {

        // Define field columns to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('columns', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'show_viewed_duration');

        // Conditionally launch add field columns.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2019101502, 'videotime');
    }

    return true;
}
