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

namespace videotimetab_venue\external;

use videotimeplugin_live\socket;
use block_deft\venue_manager;
use cache;
use context;
use context_block;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use moodle_exception;

/**
 * External function for storing user venue settings
 *
 * @package    videotimetab_venue
 * @copyright  2023 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class venue_settings extends external_api {

    /**
     * Get parameter definition for send_signal.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Block context id'),
                'mute' => new external_value(PARAM_BOOL, 'Whether audio should be muted'),
                'status' => new external_value(PARAM_BOOL, 'Whether the connection should be closed'),
                'peerid' => new external_value(PARAM_INT, 'Some other peer to change', VALUE_DEFAULT, 0),
            ]
        );
    }

    /**
     * Change settings
     *
     * @param int $contextid Context id for module
     * @param int $mute Whether to mute
     * @param int $status Whether to close
     * @param int $peerid The id of a user's peer changed by manager
     * @return array Status indicator
     */
    public static function execute($contextid, $mute, $status, $peerid): array {
        global $DB, $SESSION;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'mute' => $mute,
            'status' => $status,
            'peerid' => $peerid,
        ]);

        $context = context::instance_by_id($contextid);
        self::validate_context($context);

        require_login();
        require_capability('block/deft:joinvenue', $context);

        if (
            $context->contextlevel != CONTEXT_MODULE
            || !$cm = get_coursemodule_from_id('videotime', $context->instanceid)
        ) {
            return [
                'status' => false,
            ];
        }


        if (!empty($peerid) && $peerid != $DB->get_field_select('sessions', 'id', 'sid = ?', [session_id()])) {
            require_capability('block/deft:moderate', $context);
        } else {
            $peerid = $DB->get_field_select('sessions', 'id', 'sid = ?', [session_id()]);
        }

        if (!$record = $DB->get_record('videotimetab_venue_peer', [
            'sessionid' => $peerid,
            'videotime' => $cm->instance,
            'status' => 0,
        ])) {
            throw new moodle_exception('invalidpeer');
        }

        if (($record->mute == $mute) && ($record->status == $status)) {
            // No changes needed.
            return [
                'status' => false,
            ];
        }

        $record->mute = $mute;
        $record->status = $status;

        $DB->update_record('videotimetab_venue_peer', $record);

        $socket = new socket($context);
        $socket->dispatch();

        $params = [
            'context' => $context,
            'objectid' => $cm->instance,
        ];

        if ($status) {
            $event = \block_deft\event\venue_ended::create($params);
        } else {
            $params['other'] = ['status' => $mute];
            if (!empty($relateduserid)) {
                $params['relateduserid'] = $relateduserid;
            }
            $event = \block_deft\event\mute_switched::create($params);
        }
        $event->trigger();

        return [
            'status' => true,
        ];
    }

    /**
     * Get return definition for send_signal
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Whether changed'),
        ]);
    }
}
