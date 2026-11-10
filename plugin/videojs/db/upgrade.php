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
 * Upgrade script for the Video Time Video.js player.
 *
 * @package     videotimeplugin_videojs
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade plugin
 *
 * @param string $oldversion the version we are upgrading from.
 * @return bool
 */
function xmldb_videotimeplugin_videojs_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025092205) {
        // Define field fullscreen to be added to videotimeplugin_videojs.
        $table = new xmldb_table('videotimeplugin_videojs');
        $field = new xmldb_field('fullscreen', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'controls');

        // Conditionally launch add field fullscreen.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Videojs savepoint reached.
        upgrade_plugin_savepoint(true, 2025092205, 'videotimeplugin', 'videojs');
    }

    return true;
}
