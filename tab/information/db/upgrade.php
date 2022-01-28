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
 * Upgrade script for the Video Time Information tab.
 *
 * @package     videotimetab_information
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Uggrede plugin
 *
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_videotimetab_information_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022030100) {

        // Define field name to be added to videotimetab_information.
        $table = new xmldb_table('videotimetab_information');
        $field = new xmldb_field('name', XMLDB_TYPE_TEXT, null, null, null, null, null, 'format');

        // Conditionally launch add field name.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Information savepoint reached.
        upgrade_plugin_savepoint(true, 2022030100, 'videotimetab', 'information');
    }

    return true;
}
