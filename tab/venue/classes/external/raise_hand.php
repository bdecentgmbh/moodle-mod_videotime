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

/**
 * External function for logging hand raising events
 *
 * @package    videotimetab_venue
 * @copyright  2023 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class raise_hand extends external_api {

    /**
     * Get parameter definition for raise hand
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Block context id'),
                'status' => new external_value(PARAM_BOOL, 'Whether hand should be raised'),
            ]
        );
    }

    /**
     * Log action
     *
     * @param int $contextid Context id for module
     * @param int $status Whether to raise hand
     * @return array Status indicator
     */
    public static function execute($contextid, $status): array {
        global $DB, $SESSION;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'status' => $status,
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

        $params = [
            'context' => $context,
            'objectid' => $cm->instance,
        ];

        if ($status) {
            $event = \videotimetab_venue\event\hand_raise_sent::create($params);
        } else {
            $event = \videotimetab_venue\event\hand_lower_sent::create($params);
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
