<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps are defined here.
 *
 * @package     videotimetab_interaction
 * @category    upgrade
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute videotimetab_interaction upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_videotimetab_interaction_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026031001) {
        // Rename field spacing on table videotimetab_interaction to NEWNAMEGOESHERE.
        $table = new xmldb_table('videotimetab_interaction');
        $field = new xmldb_field('interval', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'videotime');

        // Launch rename field spacing.
        $dbman->rename_field($table, $field, 'spacing');

        // Copy settings.
        set_config('spacing', get_config('videotimetab_interaction', 'interval'), 'videotimetab_interaction');

        // Interaction savepoint reached.
        upgrade_plugin_savepoint(true, 2026031001, 'videotimetab', 'interaction');
    }

    if ($oldversion < 2026031003) {
        // Define table videotimetab_interaction_question to be created.
        $table = new xmldb_table('videotimetab_interaction_question');

        // Adding fields to table videotimetab_interaction_question.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('videotime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cueid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table videotimetab_interaction_question.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('cue', XMLDB_KEY_FOREIGN_UNIQUE, ['cueid'], 'videotimetab_interaction_cue', ['id']);
        $table->add_key('videotime', XMLDB_KEY_FOREIGN, ['videotime'], 'videotime', ['id']);

        // Conditionally launch create table for videotimetab_interaction_question.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Interaction savepoint reached.
        upgrade_plugin_savepoint(true, 2026031003, 'videotimetab', 'interaction');
    }

    return true;
}
