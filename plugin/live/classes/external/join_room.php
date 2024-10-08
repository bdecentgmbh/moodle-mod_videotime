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

use block_deft\janus;
use videotimeplugin_live\socket;
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
class join_room extends external_api {
    /**
     * Get parameter definition for raise hand
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'handle' => new external_value(PARAM_INT, 'Plugin handle id'),
                'id' => new external_value(PARAM_INT, 'Peer id for user session'),
                'plugin' => new external_value(PARAM_TEXT, 'Janus plugin name'),
                'ptype' => new external_value(PARAM_BOOL, 'Whether video pubisher', VALUE_DEFAULT, false),
                'room' => new external_value(PARAM_INT, 'Room id being joined'),
                'session' => new external_value(PARAM_INT, 'Janus session id'),
                'feed' => new external_value(PARAM_INT, 'Initial feed', VALUE_DEFAULT, 0),
            ]
        );
    }

    /**
     * Join room
     *
     * @param int $handle Janus plugin handle
     * @param string $id Context id
     * @param int $plugin Janus plugin name
     * @param bool $ptype Whether video publisher
     * @param int $room Room id being joined
     * @param int $session Janus session id
     * @param int $feed Initial video feed
     * @return array
     */
    public static function execute($handle, $id, $plugin, $ptype, $room, $session, $feed): array {
        global $DB, $SESSION, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'handle' => $handle,
            'id' => $id,
            'plugin' => $plugin,
            'ptype' => $ptype,
            'room' => $room,
            'session' => $session,
            'feed' => $feed,
        ]);

        $context = context::instance_by_id($id);
        $cm = get_coursemodule_from_id('videotime', $context->instanceid);
        self::validate_context($context);

        require_login();
        require_capability('mod/videotime:view', $context);

        $janus = new janus($session);

        $janusroom = new janus_room($cm->instance);

        $token = $janusroom->get_token();

        if ($plugin == 'janus.plugin.videoroom') {
            $message = [
                'ptype' => $ptype ? 'publisher' : 'subscriber',
                'request' => 'join',
                'room' => $room,
                'token' => $token,
            ];
            if ($feed) {
                $message['streams'] = [
                    [
                        'feed' => $feed,
                    ],
                ];
            } else {
                require_capability('block/deft:sharevideo', $context);
                $DB->set_field('videotimeplugin_live_peer', 'status', 1, [
                    'videotime' => $cm->instance,
                ]);
                $feedid = $DB->insert_record('videotimeplugin_live_peer', [
                    'sessionid' => $DB->get_field('sessions', 'id', [
                        'sid' => session_id(),
                    ]),
                    'videotime' => $cm->instance,
                    'timecreated' => time(),
                    'timemodified' => time(),
                    'userid' => $USER->id,
                ]);
                $message['id'] = $feedid;
            }
        }

        $janus->send($handle, $message);

        return [
            'status' => true,
            'id' => (int) $feedid ?? 0,
        ];
    }

    /**
     * Get return definition for hand_raise
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Whether successful'),
            'id' => new external_value(PARAM_INT, 'New video session id'),
        ]);
    }
}
