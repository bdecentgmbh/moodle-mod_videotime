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
 * @package     videotimeplugin_live
 * @category    admin
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotime/lib.php');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('defaultsettings', new lang_string('default', 'videotime') . ' ' .
        new lang_string('settings'), ''));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_live/autoplay',
        new lang_string('option_autoplay', 'videotime'),
        new lang_string('option_autoplay_help', 'videotime'),
        '1'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_live/responsive',
        new lang_string('option_responsive', 'videotime'),
        new lang_string('option_responsive_help', 'videotime'),
        '1'
    ));

    $settings->add(new admin_setting_configtext(
        'videotimeplugin_live/height',
        new lang_string('option_height', 'videotime'),
        new lang_string('option_height_help', 'videotime'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'videotimeplugin_live/width',
        new lang_string('option_width', 'videotime'),
        new lang_string('option_width_help', 'videotime'),
        '',
        PARAM_INT
    ));

    $settings->add(
        new admin_setting_configcheckbox(
            'videotimeplugin_live/controls',
            new lang_string('option_controls', 'videotime'),
            new lang_string('option_controls_help', 'videotime'),
            '1'
        )
    );

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_live/muted',
        new lang_string('option_muted', 'videotime'),
        new lang_string('option_muted_help', 'videotime'),
        '0'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_live/playsinline',
        new lang_string('option_playsinline', 'videotime'),
        new lang_string('option_playsinline_help', 'videotime'),
        '1'
    ));

    $options = [
        'accepted_types' => [
            '.png', '.jpg', '.gif', '.webp', '.tiff', '.svg',
        ],
    ];
    $settings->add(new admin_setting_configstoredfile(
        'videotimeplugin_live/posterimage',
        new lang_string('posterimage', 'videotimeplugin_live'),
        new lang_string('posterimage_desc', 'videotimeplugin_live'),
        'poster',
        0,
        $options
    ));

    $settings->add(new admin_setting_heading('forcedhdr', new lang_string('forcedsettings', 'videotime'), ''));

    $options = [
        'responsive' => new lang_string('option_responsive', 'videotime'),
        'controls' => new lang_string('option_controls', 'videotime'),
        'height' => new lang_string('option_height', 'videotime'),
        'muted' => new lang_string('option_muted', 'videotime'),
        'playsinline' => new lang_string('option_playsinline', 'videotime'),
        'width' => new lang_string('option_width', 'videotime'),
    ];

    $settings->add(new admin_setting_configmultiselect(
        'videotimeplugin_live/forced',
        new lang_string('forcedsettings', 'videotime'),
        new lang_string('forcedsettings_help', 'videotime'),
        [ ],
        $options
    ));

    $settings->add(new admin_setting_heading('advancedhdr', new lang_string('advancedsettings', 'videotime'), ''));

    $settings->add(new admin_setting_configmultiselect(
        'videotimeplugin_live/advanced',
        new lang_string('advancedsettings', 'videotime'),
        new lang_string('advancedsettings_help', 'videotime'),
        [
            'height',
            'muted',
            'playsinline',
            'width',
        ],
        $options
    ));
}
