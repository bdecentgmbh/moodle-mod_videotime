<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     videotimetab_interaction
 * @category    admin
 * @copyright   2026 bdecent gmbh <https://bdecent.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $setting = new admin_setting_configcheckbox(
        'videotimetab_interaction/default',
        new lang_string('default', 'videotimetab_interaction'),
        new lang_string('default_help', 'videotimetab_interaction'),
        1
    );

    $settings->add($setting);

    $setting = new admin_setting_configduration(
        'videotimetab_interaction/interval',
        new lang_string('interval', 'videotimetab_interaction'),
        new lang_string('interval_help', 'videotimetab_interaction'),
        MINSECS,
        PARAM_FLOAT
    );
    $setting->set_max_duration(DAYSECS);

    $settings->add($setting);

    $setting = new admin_setting_configduration(
        'videotimetab_interaction/countdown',
        new lang_string('countdown', 'videotimetab_interaction'),
        new lang_string('countdown_help', 'videotimetab_interaction'),
        10,
        PARAM_INT
    );
    $setting->set_max_duration(HOURSECS);

    $settings->add($setting);
}
