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
class venue_status extends external_api {

    /**
     * Get parameter definition for venue_status.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Videotime module context id'),
            ]
        );
    }

    /**
     * Get status
     *
     * @param int $contextid Context id for module
     * @return array Current status
     */
    public static function execute($contextid): array {
        global $DB, $SESSION;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
        ]);

        $context = context::instance_by_id($contextid);
        self::validate_context($context);

        require_login();
        require_capability('block/deft:joinvenue', $context);

        if (
            $context->contextlevel != CONTEXT_MODULE
            || !$cm = get_coursemodule_from_id('videotime', $context->instanceid)
        ) {
            throw new moodle_exception('invalidcontext');
        }


        $peerid = $DB->get_field_select('sessions', 'id', 'sid = ?', [session_id()]);

        $records = $DB->get_records('videotimetab_venue_peer', [
            'status' => 0,
            'videotime' => $cm->instance,
        ], '', 'sessionid AS id, status, mute');

        $record = $records[$peerid];

        return [
            'peers' => array_keys($records),
            'settings' => array_values($records),
        ];
    }

    /**
     * Get return definition for venue_status
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'peers' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Currently available peer ids'),
            ),
            'settings' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Peer id'),
                    'mute' => new external_value(PARAM_BOOL, 'Whether audio should be muted'),
                    'status' => new external_value(PARAM_BOOL, 'Whether connection should be closed'),
                ]),
            ),
        ]);
    }
}
