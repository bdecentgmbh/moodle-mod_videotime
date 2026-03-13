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
 * Plugin administration pages are defined here.
 *
 * @package     videotimetab_embed
 * @category    admin
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// PARAM_RAW is required because PARAM_URL strips the { } placeholder characters.
$settings->add(new admin_setting_configtext(
    'videotimetab_embed/embedurl',
    new lang_string('embedurl', 'videotimetab_embed'),
    new lang_string('embedurl_help', 'videotimetab_embed'),
    '',
    PARAM_RAW
));

$settings->add(new admin_setting_configcheckbox(
    'videotimetab_embed/default',
    new lang_string('default', 'videotimetab_embed'),
    new lang_string('default_help', 'videotimetab_embed'),
    0
));
