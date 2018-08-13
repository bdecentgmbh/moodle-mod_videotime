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
        $field = new xmldb_field('completion_on_view_time', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field completion_on_view_time.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field completion_on_view_time_second to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_on_view_time_second', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'completion_on_view_time');

        // Conditionally launch add field completion_on_view_time_second.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field completion_on_finish to be added to videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('completion_on_finish', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'completion_on_view_time_second');

        // Conditionally launch add field completion_on_finish.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videotime savepoint reached.
        upgrade_mod_savepoint(true, 2018080205, 'videotime');
    }

    return true;
}
