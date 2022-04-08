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
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Uggrede plugin
 *
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

    if ($oldversion < 2021021300) {

        // Define field preventfastforwarding to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('preventfastforwarding', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'columns');

        // Conditionally launch add field preventfastforwarding.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2021021300, 'videotime');
    }

    if ($oldversion < 2021051906) {

        // Define field completion_hide_detail to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_hide_detail', XMLDB_TYPE_INTEGER, '1', null,
            XMLDB_NOTNULL, null, '0', 'completion_on_percent_value');

        // Conditionally launch add field completion_hide_detail.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2021051906, 'videotime');
    }

    if ($oldversion < 2021081000) {

        // Define field enabletabs to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('enabletabs', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'preventfastforwarding');

        // Conditionally launch add field enabletabs.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field autopause to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('autopause', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'enabletabs');

        // Conditionally launch add field autopause.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field background to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('background', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'autopause');

        // Conditionally launch add field background.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field controls to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('controls', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'background');

        // Conditionally launch add field controls.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field pip to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('pip', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'controls');

        // Conditionally launch add field pip.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field dnt to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('dnt', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'pip');

        // Conditionally launch add field dnt.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2021081000, 'videotime');
    }

    if ($oldversion < 2022022100) {

        // Changing the default of field controls on table videotime to 1.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('controls', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'background');

        // Launch change of default for field controls.
        $dbman->change_field_default($table, $field);

        $DB->set_field('videotime', 'controls', 1, array('controls' => 0));

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2022022100, 'videotime');
    }

    if ($oldversion < 2022022800) {

        // Changing nullability of field height on table videotime to not null.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('height', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'color');

        // Launch change of nullability for field height.
        $dbman->change_field_notnull($table, $field);

        // Changing nullability of field maxheight on table videotime to not null.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('maxheight', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'height');

        // Launch change of nullability for field maxheight.
        $dbman->change_field_notnull($table, $field);

        // Changing nullability of field maxwidth on table videotime to not null.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('maxwidth', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'maxheight');

        // Launch change of nullability for field maxwidth.
        $dbman->change_field_notnull($table, $field);

        // Changing nullability of field width on table videotime to not null.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('width', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'dnt');

        // Launch change of nullability for field width.
        $dbman->change_field_notnull($table, $field);

        // Changing the default of field autopause on table videotime to 1.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('autopause', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'transparent');

        // Launch change of default for field autopause.
        $dbman->change_field_default($table, $field);

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2022022800, 'videotime');
    }

    if ($oldversion < 2022022802) {

        $DB->set_field('videotime', 'controls', 1, array('controls' => 0));

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2022022802, 'videotime');
    }

    if ($oldversion < 2022030104) {
        // Assign view_report to editing teacher if assigned to non editing teacher.
        $context = context_system::instance();
        $roles = $DB->get_records_menu('role', array(), '', 'shortname, id');
        $capabilities = $DB->get_records_menu('role_capabilities', array(
            'contextid' => $context->id,
            'capability' => 'mod/videotime:view_report'
        ), '', 'roleid, permission');
        if (
            key_exists('editingteacher', $roles)
            && !key_exists($roles['editingteacher'], $capabilities)
            && key_exists('teacher', $roles)
            && $capabilities[$roles['teacher']] === (string)CAP_ALLOW
        ) {
            assign_capability('mod/videotime:view_report', CAP_ALLOW,
                    $roles['editingteacher'], $context->id, true);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2022030104, 'videotime');
    }

    if ($oldversion < 2022040801) {

        // Define field show_description_in_player to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_description_in_player', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'show_description');

        // Conditionally launch add field show_description_in_player.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2022040801, 'videotime');
    }

    return true;
}
