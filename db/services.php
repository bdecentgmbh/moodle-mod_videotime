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
 * External functions and service definitions.
 *
 * @package     mod_videotime
 * @copyright   2020 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_videotime_get_videotime' => [
        'classpath'     => '',
        'classname'     => 'mod_videotime\external\external',
        'methodname'    => 'get_videotime',
        'description'   => 'Get VideoTime activity module instance.',
        'type'          => 'read',
        'ajax'          => true,
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ],
    'mod_videotime_view_videotime' => [
        'classpath'     => '',
        'classname'     => 'mod_videotime\external\external',
        'methodname'    => 'view_videotime',
        'description'   => 'Log the user viewing a video.',
        'type'          => 'write',
        'ajax'          => true,
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
    ]
];
