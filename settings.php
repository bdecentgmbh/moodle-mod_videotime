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
 * @package     mod_videotime
 * @category    admin
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/videotime/lib.php');

    if (!videotime_has_pro()) {
        $settings->add(new admin_setting_heading('pro', '', html_writer::link(new moodle_url('https://link.bdecent.de/videotimepro3'),
            html_writer::img('https://link.bdecent.de/videotimepro3/image.jpg', '',
                ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px']))));
    }

    if (videotime_has_pro()) {
        $settings->add(new admin_setting_heading('option_responsive', get_string('default', 'videotime') . ' ' .
            get_string('option_responsive', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/responsive', get_string('option_responsive', 'videotime'),
            get_string('option_responsive_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/responsive_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_height', get_string('default', 'videotime') . ' ' .
            get_string('option_height', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/height', get_string('option_height', 'videotime'),
            get_string('option_height_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/height_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_width', get_string('default') . ' ' .
            get_string('option_width', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/width', get_string('option_width', 'videotime'),
            get_string('option_width_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/width_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_maxheight', get_string('default') . ' ' .
            get_string('option_maxheight', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/maxheight', get_string('option_maxheight', 'videotime'),
            get_string('option_maxheight_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/maxheight_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_maxwidth', get_string('default') . ' ' .
            get_string('option_maxwidth', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/maxwidth', get_string('option_maxwidth', 'videotime'),
            get_string('option_maxwidth_help', 'videotime'), null, PARAM_INT));
        $settings->add(new admin_setting_configcheckbox('videotime/maxwidth_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_autoplay', get_string('default') . ' ' .
            get_string('option_autoplay', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/autoplay', get_string('option_autoplay', 'videotime'),
            get_string('option_autoplay_help', 'videotime'), '0'));
        $settings->add(new admin_setting_configcheckbox('videotime/autoplay_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_byline', get_string('default') . ' ' .
            get_string('option_byline', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/byline', get_string('option_byline', 'videotime'),
            get_string('option_byline_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/byline_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_color', get_string('default') . ' ' .
            get_string('option_color', 'videotime'), ''));
        $settings->add(new admin_setting_configtext('videotime/color', get_string('option_color', 'videotime'),
            get_string('option_color_help', 'videotime'), '00adef', PARAM_TEXT));
        $settings->add(new admin_setting_configcheckbox('videotime/color_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_muted', get_string('default') . ' ' .
            get_string('option_muted', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/muted', get_string('option_muted', 'videotime'),
            get_string('option_muted_help', 'videotime'), '0'));
        $settings->add(new admin_setting_configcheckbox('videotime/muted_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_playsinline', get_string('default') . ' ' .
            get_string('option_playsinline', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/playsinline', get_string('option_playsinline', 'videotime'),
            get_string('option_playsinline_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/playsinline_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_portrait', get_string('default') . ' ' .
            get_string('option_portrait', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/portrait', get_string('option_portrait', 'videotime'),
            get_string('option_portrait_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/portrait_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_speed', get_string('default') . ' ' .
            get_string('option_speed', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/speed', get_string('option_speed', 'videotime'),
            get_string('option_speed_help', 'videotime'), '0'));
        $settings->add(new admin_setting_configcheckbox('videotime/speed_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_title', get_string('default') . ' ' .
            get_string('option_title', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/title', get_string('option_title', 'videotime'),
            get_string('option_title_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/title_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));

        $settings->add(new admin_setting_heading('option_transparent', get_string('default') . ' ' .
            get_string('option_transparent', 'videotime'), ''));
        $settings->add(new admin_setting_configcheckbox('videotime/transparent', get_string('option_transparent', 'videotime'),
            get_string('option_transparent_help', 'videotime'), '1'));
        $settings->add(new admin_setting_configcheckbox('videotime/transparent_force', get_string('force', 'videotime'),
            get_string('force_help', 'videotime'), '0'));
    }

    if (!videotime_has_pro()) {
        $settings->add(new admin_setting_heading('pro2', '', html_writer::link(new moodle_url('https://link.bdecent.de/videotimepro4'),
            html_writer::img('https://link.bdecent.de/videotimepro4/image.jpg', '',
                ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px']))));
    }
}
