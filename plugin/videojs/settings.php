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
 * @package     videotimeplugin_videojs
 * @category    admin
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/videotime/lib.php');

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('option_responsive', get_string('default', 'videotime') . ' ' .
        get_string('option_responsive', 'videotime'), ''));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/responsive', get_string('option_responsive', 'videotime'),
        get_string('option_responsive_help', 'videotime'), '1'));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/responsive_force', get_string('force', 'videotime'),
        get_string('force_help', 'videotime'), '0'));

    $settings->add(new admin_setting_heading('option_height', get_string('default', 'videotime') . ' ' .
        get_string('option_height', 'videotime'), ''));
    $settings->add(new admin_setting_configtext('videotimeplugin_videojs/height', get_string('option_height', 'videotime'),
        get_string('option_height_help', 'videotime'), '', PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/height_force', get_string('force', 'videotime'),
        get_string('force_help', 'videotime'), '0'));

    $settings->add(new admin_setting_heading('option_width', get_string('default') . ' ' .
        get_string('option_width', 'videotime'), ''));
    $settings->add(new admin_setting_configtext('videotimeplugin_videojs/width', get_string('option_width', 'videotime'),
        get_string('option_width_help', 'videotime'), '', PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/width_force', get_string('force', 'videotime'),
        get_string('force_help', 'videotime'), '0'));

    $settings->add(new admin_setting_heading('option_controls', get_string('default', 'videotime') . ' ' .
        get_string('option_controls', 'videotime'), ''));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/controls', get_string('option_controls', 'videotime'),
        get_string('option_controls_help', 'videotime'), '1'));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/controls_force', get_string('force', 'videotime'),
        get_string('force_help', 'videotime'), '0'));

    $settings->add(new admin_setting_heading('option_loop', get_string('default', 'videotime') . ' ' .
        get_string('option_loop', 'videotimeplugin_videojs'), ''));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/loop', get_string('option_loop', 'videotimeplugin_videojs'),
        get_string('option_loop_help', 'videotimeplugin_videojs'), '1'));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/loop_force', get_string('force', 'videotime'),
        get_string('force_help', 'videotime'), '0'));

    $settings->add(new admin_setting_heading('option_muted', get_string('default', 'videotime') . ' ' .
        get_string('option_muted', 'videotime'), ''));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/muted', get_string('option_muted', 'videotime'),
        get_string('option_muted_help', 'videotime'), '1'));
    $settings->add(new admin_setting_configcheckbox('videotimeplugin_videojs/muted_force', get_string('force', 'videotime'),
        get_string('force_help', 'videotime'), '0'));

    $settings->add(new admin_setting_configmultiselect(
        'videotimeplugin_videojs/advanced',
        new lang_string('advancedsettings', 'videotime'),
        new lang_string('advancedsettings_help', 'videotime'),
        [ ],
        [
            'responsive' => new lang_string('option_responsive', 'videotime'),
            'controls' => new lang_string('option_controls', 'videotime'),
            'height' => new lang_string('option_height', 'videotime'),
            'muted' => new lang_string('option_muted', 'videotime'),
            'option_loop' => new lang_string('option_loop', 'videotimeplugin_videojs'),
            'speed' => new lang_string('option_speed', 'videotime'),
            'width' => new lang_string('option_width', 'videotime'),
        ]
    ));
}
