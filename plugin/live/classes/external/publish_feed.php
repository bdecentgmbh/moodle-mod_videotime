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
use context;
use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use stdClass;
use videotimeplugin_live\janus_room;

/**
 * External function to offer feed to venue
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publish_feed extends external_api {
    /**
     * Get parameter definition for raise hand
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'Peer id for user session'),
                'publish' => new external_value(PARAM_BOOL, 'Whhether to publish or not', VALUE_DEFAULT, true),
                'room' => new external_value(PARAM_INT, 'Room id being joined'),
            ]
        );
    }

    /**
     * Publish feed
     *
     * @param string $id Peer id
     * @param bool $publish Whether to publish
     * @param int $room Room id being joined
     * @return array
     */
    public static function execute($id, $publish, $room): array {
        global $DB, $SESSION, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'id' => $id,
            'publish' => $publish,
            'room' => $room,
        ]);

        $record = $DB->get_record(
            'block_deft_room',
            [
                'roomid' => $room,
                'component' => 'videotimeplugin_live',
            ]
        );

        if (
            !$DB->get_record_select(
                'videotimeplugin_live_peer',
                "id = :id AND status = 0 AND sessionid IN (SELECT id FROM {sessions} WHERE sid = :sid)",
                [
                'id' => $id,
                'sid' => session_id(),
                ]
            )
        ) {
            return [
                'status' => false,
            ];
        }

        $cm = get_coursemodule_from_instance('videotime', $record->itemid);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_login();
        require_capability('mod/videotime:view', $context);
        if ($publish) {
            require_capability('videotimeplugin/live:sharevideo', $context);
        }

        $data = json_decode($record->data) ?? new stdClass();
        if (!$publish && !empty($data->feed) && $data->feed == $id) {
            $data->feed = 0;
            $DB->set_field('videotimeplugin_live_peer', 'status', 1, [
                'id' => $id,
            ]);
        } else if ($publish) {
            if (
                !empty($data->feed)
                && ($data->feed != $id)
                && $DB->get_record('videotimeplugin_live_peer', [
                    'videotime' => $cm->instance,
                    'userid' => $USER->id,
                ])
            ) {
                require_capability('videotimeplugin/live:moderate', $context);
            }
            $data->feed = $id;
        } else {
            return [
                'status' => false,
            ];
        }

        $record->timemodified = time();
        $record->data = json_encode($data);
        $DB->update_record('block_deft_room', $record);

        $socket = new socket($context);
        $socket->dispatch();

        $params = [
            'context' => $context,
            'objectid' => $record->itemid,
        ];

        if ($publish) {
            $event = \videotimeplugin_live\event\video_started::create($params);
        } else {
            $event = \videotimeplugin_live\event\video_ended::create($params);
        }
        $event->trigger();

        return [
            'status' => true,
        ];
    }

    /**
     * Get return definition for hand_raise
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Whether changed'),
        ]);
    }
}
