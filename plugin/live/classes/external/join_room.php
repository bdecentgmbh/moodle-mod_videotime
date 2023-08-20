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
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use videotimeplugin_live\janus_room;

/**
 * External function for joining Janus gateway
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class join_room extends \block_deft\external\join_room {

    /**
     * Join room
     *
     * @param int $handle Janus plugin handle
     * @param string $id Venue peer id
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

        if (!empty($id) && !$DB->get_record('sessions', [
            'id' => $id,
            'sid' => session_id(),
        ])) {
            return [
                'status' => false,
            ];
        }
        $record = $DB->get_record(
            'block_deft_room',
            [
                'roomid' => $room,
                'component' => 'videotimeplugin_live',
            ]
        );

        $cm = get_coursemodule_from_instance('videotime', $record->itemid);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_login();
        require_capability('mod/videotime:view', $context);

        $janus = new janus($session);
        $janusroom = new janus_room($record->itemid);

        $token = $janusroom->get_token();

        if ($plugin == 'janus.plugin.videoroom') {
            if (empty($id)) {
                $id = $janus->transaction_identifier();
            }
            if ($ptype) {
                $message = [
                    'id' => $id,
                    'request' => 'kick',
                    'room' => $room,
                    'secret' => $janusroom->get_secret(),
                ];

                $janus->send($handle, $message);
            }
            $message = [
                'id' => $ptype ? $id : $id . 'subscriber',
                'ptype' => $ptype ? 'publisher' : 'subscriber',
                'request' => 'join',
                'room' => $room,
                'token' => $token,
            ];
            if ($feed) {
                $message['streams'] = [
                    [
                        'feed' => $feed
                    ]
                ];
            } else {
                require_capability('videotimeplugin/live:sharevideo', $context);
            }
        } else {
            $textroom = $janus->attach('janus.plugin.videoroom');
            $janus->send($textroom, [
                'request' => 'kick',
                'room' => $room,
                'secret' => $janusroom->get_secret(),
                'username' => $id,
            ]);

            $janus->send($handle, [
                'id' => $id,
                'request' => 'kick',
                'room' => $room,
                'secret' => $janusroom->get_secret(),
            ]);

            $message = [
                'id' => $id,
                'request' => 'join',
                'room' => $room,
                'token' => $token,
            ];
            $params = [
                'context' => $context,
                'objectid' => $cm->instance,
            ];

            $event = \videotimetab_venue\event\audiobridge_launched::create($params);
            $event->trigger();

            $sessionid = $DB->get_field_select('sessions', 'id', 'sid = :sid', ['sid' => session_id()]);

            $timenow = time();

            if ($record = $DB->get_record('videotimetab_venue_peer', [
                'sessionid' => $sessionid,
                'videotime' => $cm->instance,
                'userid' => $USER->id,
                'status' => false,
            ])) {
                $record->timemodified = $timenow;
                $DB->update_record('videotimetab_venue_peer', $record);
            } else {
                $DB->insert_record('videotimetab_venue_peer', [
                    'sessionid' => $sessionid,
                    'videotime' => $cm->instance,
                    'userid' => $USER->id,
                    'mute' => true,
                    'status' => false,
                    'timecreated' => $timenow,
                    'timemodified' => $timenow,
                ]);
            }
        }

        $response = $janus->send($handle, $message);

        return [
            'status' => true,
        ];
    }
}
