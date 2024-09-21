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
use cache;
use context;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use stdClass;

/**
 * External function for storing user venue settings
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_feed extends external_api {
    /**
     * Get parameter definition for send_signal.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Context id for module'),
            ]
        );
    }

    /**
     * Get settings
     *
     * @param int $contextid Module context id of activity
     * @return array Status indicator
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

        $record = $DB->get_record(
            'block_deft_room',
            [
                'itemid' => $cm->instance,
                'component' => 'videotimeplugin_live',
            ]
        );

        $data = json_decode($record->data) ?? new stdClass();

        if (
            empty($data->feed || !$DB->get_record(
                'videotimeplugin_live_peer',
                [
                'id' => $data->feed,
                'status' => 0,
                ]
            ))
        ) {
            return [
                'feed' => 0,
            ];
        }

        return [
            'feed' => $data->feed ?? 0,
        ];
    }

    /**
     * Get return definition for send_signal
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'feed' => new external_value(PARAM_INT, 'ID of publisher'),
        ]);
    }
}
