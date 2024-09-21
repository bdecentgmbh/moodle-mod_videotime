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

require_once($CFG->dirroot . '/mod/videotime/lib.php');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('defaultsettings', get_string('default', 'videotime') . ' ' .
        get_string('settings'), ''));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_videojs/autoplay',
        get_string('option_autoplay', 'videotime'),
        get_string('option_autoplay_help', 'videotime'),
        '1'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_videojs/responsive',
        get_string('option_responsive', 'videotime'),
        get_string('option_responsive_help', 'videotime'),
        '1'
    ));

    $settings->add(new admin_setting_configtext(
        'videotimeplugin_videojs/height',
        get_string('option_height', 'videotime'),
        get_string('option_height_help', 'videotime'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'videotimeplugin_videojs/width',
        get_string('option_width', 'videotime'),
        get_string('option_width_help', 'videotime'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_videojs/controls',
        get_string('option_controls', 'videotime'),
        get_string('option_controls_help', 'videotime'),
        '1'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_videojs/option_loop',
        get_string('option_loop', 'videotime'),
        get_string('option_loop_help', 'videotime'),
        '0'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_videojs/muted',
        get_string('option_muted', 'videotime'),
        get_string('option_muted_help', 'videotime'),
        '0'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_videojs/playsinline',
        get_string('option_playsinline', 'videotime'),
        get_string('option_playsinline_help', 'videotime'),
        '1'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_videojs/speed',
        get_string('option_speed', 'videotime'),
        get_string('option_speed_help', 'videotime'),
        '1'
    ));

    $options = [
        'accepted_types' => [
            '.png', '.jpg', '.gif', '.webp', '.tiff', '.svg',
        ],
    ];
    $settings->add(new admin_setting_configstoredfile(
        'videotimeplugin_videojs/audioimage',
        new lang_string('audioimage', 'videotimeplugin_videojs'),
        new lang_string('audioimage_desc', 'videotimeplugin_videojs'),
        'audioimage',
        0,
        $options
    ));

    $settings->add(new admin_setting_heading('forcedhdr', get_string('forcedsettings', 'videotime'), ''));

    $options = [
        'autoplay' => new lang_string('option_autoplay', 'videotime'),
        'responsive' => new lang_string('option_responsive', 'videotime'),
        'controls' => new lang_string('option_controls', 'videotime'),
        'height' => new lang_string('option_height', 'videotime'),
        'muted' => new lang_string('option_muted', 'videotime'),
        'option_loop' => new lang_string('option_loop', 'videotime'),
        'playsinline' => new lang_string('option_playsinline', 'videotime'),
        'speed' => new lang_string('option_speed', 'videotime'),
        'width' => new lang_string('option_width', 'videotime'),
    ];

    $settings->add(new admin_setting_configmultiselect(
        'videotimeplugin_videojs/forced',
        new lang_string('forcedsettings', 'videotime'),
        new lang_string('forcedsettings_help', 'videotime'),
        [ ],
        $options
    ));

    $settings->add(new admin_setting_heading('advancedhdr', get_string('advancedsettings', 'videotime'), ''));

    $settings->add(new admin_setting_configmultiselect(
        'videotimeplugin_videojs/advanced',
        new lang_string('advancedsettings', 'videotime'),
        new lang_string('advancedsettings_help', 'videotime'),
        [
            'height',
            'muted',
            'option_loop',
            'playsinline',
            'width',
        ],
        $options
    ));
}
