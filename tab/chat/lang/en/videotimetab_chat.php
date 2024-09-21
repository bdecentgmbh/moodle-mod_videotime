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
 * Plugin strings are defined here.
 *
 * @package     videotimetab_chat
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachedef_comments'] = 'Video time chat comments';
$string['chattab_name'] = 'Custom tab name';
$string['default'] = 'Default';
$string['default_help'] = 'Whether tab is enabled by default';
$string['label'] = 'Chat';
$string['pluginname'] = 'Video Time Chat tab';
$string['privacy:metadata'] = 'The Video Time Chat tab plugin does not store any personal data.';
$string['privacy:metadata:core_comment'] = 'A record of comments added.';
$string['privacy:metadata:lti_client'] = 'LTI connection to deftly.us';
$string['privacy:metadata:lti_client:context'] = 'The Video Time chat tab uses the Deft response block. The Deft response block configures an external LTI connection to send messages that user information in a particular Moodle context may have been updated; however, no actual infomation is exported. The block loads a client that connects to the external site to recieve messages, but does not provide information other than establishing the connection.';
$string['upgradeplugin'] = 'This plugin requires installation of Deft response block to enable.';
