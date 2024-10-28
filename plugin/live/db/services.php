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
 * Popup activies format external functions and service definitions.
 *
 * @package     videotimeplugin_live
 * @category    external
 * @copyright   2023 bdecent gmbh <https://bdecent.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [

    'videotimeplugin_live_get_feed' => [
        'classname' => '\\videotimeplugin_live\\external\\get_feed',
        'methodname' => 'execute',
        'description' => 'Get currect video feed',
        'type' => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'videotimeplugin_live_get_room' => [
        'classname' => '\\videotimeplugin_live\\external\\get_room',
        'methodname' => 'execute',
        'description' => 'Get currect room parameters for module',
        'type' => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'videotimeplugin_live_join_room' => [
        'classname' => '\\videotimeplugin_live\\external\\join_room',
        'methodname' => 'execute',
        'description' => 'Join Janus room',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'videotimeplugin_live_publish_feed' => [
        'classname' => '\\videotimeplugin_live\\external\\publish_feed',
        'methodname' => 'execute',
        'description' => 'Publish a video feed',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'videotimeplugin_live_renew_token' => [
        'classname' => '\\videotimeplugin_live\\external\\renew_token',
        'methodname' => 'execute',
        'description' => 'Get new token to access message service',
        'type' => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
