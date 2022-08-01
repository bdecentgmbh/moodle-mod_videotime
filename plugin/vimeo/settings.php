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
 * @package     videotimeplugin_vimeo
 * @category    admin
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/videotime/lib.php');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('defaultsettings', get_string('default', 'videotime') . ' ' .
        get_string('settings'), ''));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_vimeo/responsive', get_string('option_responsive', 'videotime'),
        get_string('option_responsive_help', 'videotime'), '1'));

    $settings->add(new admin_setting_configtext('videotimeplugin_vimeo/height', get_string('option_height', 'videotime'),
        get_string('option_height_help', 'videotime'), '', PARAM_INT));

    $settings->add(new admin_setting_configtext('videotimeplugin_vimeo/width', get_string('option_width', 'videotime'),
        get_string('option_width_help', 'videotime'), '', PARAM_INT));

    $settings->add(new admin_setting_configtext('videotimeplugin_pro/color', new lang_string('option_color', 'videotime'),
        new lang_string('option_color_help', 'videotime'), '00adef', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('videotimeplugin_vimeo/controls', get_string('option_controls', 'videotime'),
        get_string('option_controls_help', 'videotime'), '1'));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_vimeo/loop', get_string('option_loop', 'videotimeplugin_vimeo'),
        get_string('option_loop_help', 'videotimeplugin_vimeo'), '1'));

    $settings->add(new admin_setting_configcheckbox('videotimeplugin_vimeo/muted', get_string('option_muted', 'videotime'),
        get_string('option_muted_help', 'videotime'), '1'));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_vimeo/playsinline', get_string('option_playsinline', 'videotime'),
        get_string('option_playsinline_help', 'videotime'), '1'));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_vimeo/portrait', get_string('option_portrait', 'videotime'),
        get_string('option_portrait_help', 'videotime'), '1'));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_vimeo/speed', get_string('option_speed', 'videotime'),
        get_string('option_speed_help', 'videotime'), '1'));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_vimeo/title', get_string('option_title', 'videotime'),
        get_string('option_title_help', 'videotime'), '1'));

    $settings->add(new admin_setting_configcheckbox(
        'videotimeplugin_vimeo/transparent', get_string('option_transparent', 'videotime'),
        get_string('option_transparent_help', 'videotime'), '1'));

    $settings->add(new admin_setting_heading('forcedhdr', get_string('forcedsettings', 'videotime'), ''));

    $options = [
        'autoplay' => new lang_string('option_autoplay', 'videotime'),
        'byline' => new lang_string('option_byline', 'videotime'),
        'color' => new lang_string('option_color', 'videotime'),
        'height' => new lang_string('option_height', 'videotime'),
        'maxheight' => new lang_string('option_maxheight', 'videotime'),
        'maxwidth' => new lang_string('option_maxwidth', 'videotime'),
        'muted' => new lang_string('option_muted', 'videotime'),
        'option_loop' => new lang_string('option_loop', 'videotimeplugin_vimeo'),
        'playsinline' => new lang_string('option_playsinline', 'videotime'),
        'portrait' => new lang_string('option_portrait', 'videotime'),
        'responsive' => new lang_string('option_responsive', 'videotime'),
        'speed' => new lang_string('option_speed', 'videotime'),
        'title' => new lang_string('option_title', 'videotime'),
        'transparent' => new lang_string('option_transparent', 'videotime'),
        'width' => new lang_string('option_width', 'videotime'),
    ];
    $settings->add(new admin_setting_configmultiselect(
        'videotimeplugin_vimeo/forced',
        new lang_string('forcedsettings', 'videotime'),
        new lang_string('forcedsettings_help', 'videotime'),
        [ ],
        $options
    ));

    $settings->add(new admin_setting_heading('advancedhdr', get_string('advancedsettings', 'videotime'), ''));

    $settings->add(new admin_setting_configmultiselect(
        'videotimeplugin_vimeo/advanced',
        new lang_string('advancedsettings', 'videotime'),
        new lang_string('advancedsettings_help', 'videotime'),
        [ ],
        $options
    ));
}
