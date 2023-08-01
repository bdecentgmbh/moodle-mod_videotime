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
 * Core external functions and service definitions.
 *
 * @package    videotimetab_venue
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'videotimetab_venue_settings' => [
        'classname' => '\\videotimetab_venue\\external\\venue_settings',
        'methodname' => 'execute',
        'description' => 'Change peer settings in venue',
        'type' => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ],

    'videotimetab_venue_status' => [
        'classname' => '\\videotimetab_venue\\external\\venue_status',
        'methodname' => 'execute',
        'description' => 'Return peer status in venue',
        'type' => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ],

    'videotimetab_venue_raise_hand' => [
        'classname' => '\\videotimetab_venue\\external\\raise_hand',
        'methodname' => 'execute',
        'description' => 'Change status for raised hand',
        'type' => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ],
];
