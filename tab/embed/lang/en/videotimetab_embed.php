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
 * @package     videotimetab_embed
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['default']          = 'Default';
$string['embedtab_name']    = 'Custom tab name';
$string['default_help']     = 'Whether the tab is enabled by default for new activities.';
$string['embedurl']         = 'Embed URL';
$string['embedurl_help']    = 'URL of the tool to embed as an iframe. Use placeholders to inject context values — each placeholder is replaced with its URL-encoded value at render time. Only placeholders you include in the URL are used; unused ones are ignored.

Available placeholders:
{username} — Moodle username
{firstname} — User\'s first name
{email} — User\'s email address
{courseshortname} — Course short name
{coursefullname} — Course full name
{courseidnumber} — Course ID number
{vtidnumber} — Video Time activity ID number
{videoid} — Vimeo video ID (numeric, extracted from the activity URL)

Example: https://myapp.com/tool?user={username}&email={email}&course={courseshortname}';
$string['label']            = 'Embed';
$string['pluginname']       = 'Embed tab';
$string['privacy:metadata'] = 'The Embed tab plugin does not store any personal data.';
$string['upgradeplugin']    = 'This plugin requires installation of Video Time Pro to enable.';
