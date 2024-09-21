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
 * Task for cleaning venue for Video Time Live
 *
 * @package   videotimeplugin_live
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace videotimeplugin_live\task;

use videotimeplugin_live\janus_room;

/**
 * Task for cleaning venue for Video Time Live
 *
 * @package   videotimeplugin_live
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup extends \core\task\scheduled_task {
    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('cleanuptask', 'videotimeplugin_live');
    }

    /**
     * Remove old entries from table videotimeplugin_live_peer
     */
    public function execute() {
        global $DB;

        $count = count($DB->get_records_select(
            'videotimeplugin_live_peer',
            'id IN (SELECT p.id
                      FROM {videotimeplugin_live_peer} p
                 LEFT JOIN {sessions} s ON p.sessionid = s.id
                     WHERE s.id IS NULL)'
        ));

        if (!$count) {
            return;
        }

        $peers = $DB->get_records_sql(
            "SELECT p.*, r.id AS roomid FROM {videotimeplugin_live_peer} p
               JOIN {block_deft_room} r ON r.itemid = p.videotime
              WHERE status = 0 AND component = 'videotimeplugin_live'
           ORDER BY roomid"
        );

        $count = $DB->delete_records_select(
            'videotimeplugin_live_peer',
            'status = 1 OR id IN (SELECT p.id
                      FROM {videotimeplugin_live_peer} p
                 LEFT JOIN {sessions} s ON p.sessionid = s.id
                     WHERE s.id IS NULL)'
        );

        mtrace("$count old peers deleted");
    }
}
