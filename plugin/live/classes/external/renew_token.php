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
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function for getting new token
 *
 * @package    videotimeplugin_live
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renew_token extends external_api {
    /**
     * Get parameter definition for renew_token.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'contextid' => new external_value(PARAM_INT, 'Block context id'),
            ]
        );
    }

    /**
     * Get new token
     *
     * @param int $contextid The block context id
     * @return array
     */
    public static function execute($contextid): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
        ]);
        $contextid = $params['contextid'];

        $context = context::instance_by_id($contextid);
        self::validate_context($context);

        $socket = new socket($context);
        $token = $socket->get_token();

        return [
            'token' => $token,
        ];
    }

    /**
     * Get return definition for renew_token
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'token' => new external_value(PARAM_ALPHANUM, 'Valid authentication token for deftly.us'),
        ]);
    }
}
