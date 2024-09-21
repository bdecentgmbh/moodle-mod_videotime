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

namespace videotimeplugin_live\external;

use videotimeplugin_live\socket;
use block_deft\janus;
use block_deft\venue_manager;
use cache;
use context;
use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use videotimeplugin_live\janus_room;

/**
 * External function for joining Janus gateway
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_room extends external_api {
    /**
     * Get parameter definition for get room
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Context id for videotime mod'),
            ]
        );
    }

    /**
     * Get room information
     *
     * @param int $contextid Video Time module context id
     * @return array
     */
    public static function execute($contextid): array {
        global $DB, $SESSION;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
        ]);

        $context = context::instance_by_id($contextid);
        self::validate_context($context);
        $cm = get_coursemodule_from_id('videotime', $context->instanceid);

        require_login();
        require_capability('mod/videotime:view', $context);

        $janusroom = new janus_room($cm->instance);
        $socket = new socket($context);

        $janus = new janus();
        $textroom = $janus->attach('janus.plugin.textroom');
        $janus->send($textroom, [
            'request' => 'kick',
            'room' => $janusroom->get_roomid(),
            'secret' => $janusroom->get_secret(),
            'username' => $DB->get_field_select('sessions', 'id', 'sid = ?', [session_id()]),
        ]);

        return [
            'roomid' => $janusroom->get_roomid(),
            'iceservers' => json_encode($socket->ice_servers()),
            'server' => $janusroom->get_server(),
        ];
    }

    /**
     * Get return definition for hand_raise
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'iceservers' => new external_value(PARAM_TEXT, 'JSON ICE server information'),
            'roomid' => new external_value(PARAM_TEXT, 'Video room id'),
            'server' => new external_value(PARAM_TEXT, 'Server url for room'),
        ]);
    }
}
