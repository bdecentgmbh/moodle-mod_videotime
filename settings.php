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
 * @copyright   2021 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\videotime_instance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotime/lib.php');

$ADMIN->add('modsettings', new admin_category(
    'modvideotimefolder',
    new lang_string('pluginname', 'videotime'),
    $module->is_enabled() === false
));

$settings = new admin_settingpage(
    'generalsettings',
    get_string('generalsettings', 'videotime'),
    'moodle/site:config'
);

if ($ADMIN->fulltree) {
    if (!videotime_has_pro()) {
        $settings->add(new admin_setting_heading(
            'pro',
            '',
            html_writer::link(
                new moodle_url('https://link.bdecent.de/videotimepro3'),
                html_writer::img(
                    'https://link.bdecent.de/videotimepro3/image.jpg',
                    '',
                    ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px']
                )
            )
        ));
    }

    $settings->add(new admin_setting_configcheckbox(
        'videotime/show_description_in_player',
        new lang_string('default', 'videotime') . ' ' .
        new lang_string('show_description_in_player', 'videotime'),
        new lang_string('show_description_in_player_help', 'videotime'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotime/enabletabs',
        new lang_string('default', 'videotime') . ' ' .
        new lang_string('enabletabs', 'videotime'),
        new lang_string('enabletabs_help', 'videotime'),
        0
    ));

    $settings->add(new admin_setting_configselect(
        'videotime/defaulttabsize',
        get_string('defaulttabsize', 'videotime'),
        get_string('defaulttabsize_help', 'videotime'),
        'videotime-size-6',
        [
            'videotime-size-3' => get_string('panelwidthsmall', 'videotime'),
            'videotime-size-6' => get_string('panelwidthmedium', 'videotime'),
            'videotime-size-9' => get_string('panelwidthlarge', 'videotime'),
        ]
    ));

    $settings->add(new admin_setting_configcheckbox(
        'videotime/mobileiframe',
        new lang_string('mobileiframe', 'videotime'),
        new lang_string('mobileiframe_help', 'videotime'),
        0
    ));

    if (!videotime_has_pro()) {
        $settings->add(new admin_setting_heading(
            'pro2',
            '',
            html_writer::link(
                new moodle_url('https://link.bdecent.de/videotimepro4'),
                html_writer::img(
                    'https://link.bdecent.de/videotimepro4/image.jpg',
                    '',
                    ['width' => '100%', 'class' => 'img-responsive', 'style' => 'max-width:700px']
                )
            )
        ));
    }
}

$ADMIN->add('modvideotimefolder', $settings);
// Tell core we already added the settings structure.
$settings = null;

if (videotime_has_pro() && videotime_has_repository()) {
    $ADMIN->add('modvideotimefolder', new admin_externalpage(
        'authenticate',
        get_string('authenticate_vimeo', 'videotime'),
        new moodle_url('/mod/videotime/plugin/repository/authenticate.php'),
        'moodle/site:config',
        false
    ));

    $ADMIN->add('modvideotimefolder', new admin_externalpage(
        'overview',
        get_string('vimeo_overview', 'videotime'),
        new moodle_url('/mod/videotime/plugin/repository/overview.php')
    ));
}

$ADMIN->add('modvideotimefolder', new admin_category(
    'videotimetabplugins',
    new lang_string('videotimetabplugins', 'videotime'),
    !$module->is_enabled()
));
$ADMIN->add('videotimetabplugins', new admin_externalpage(
    'managevideotimetabplugins',
    get_string('managevideotimetabplugins', 'videotime'),
    new moodle_url('/mod/videotime/adminmanageplugins.php', ['subtype' => 'videotimetab'])
));

foreach (core_plugin_manager::instance()->get_plugins_of_type('videotimetab') as $plugin) {
    $plugin->load_settings($ADMIN, 'videotimetabplugins', $hassiteconfig);
}

$ADMIN->add('modvideotimefolder', new admin_category(
    'videotimepluginplugins',
    new lang_string('subplugintype_videotimeplugin_plural', 'videotime'),
    !$module->is_enabled()
));
$ADMIN->add('videotimepluginplugins', new admin_externalpage(
    'managevideotimepluginplugins',
    get_string('managevideotimepluginplugins', 'videotime'),
    new moodle_url('/mod/videotime/adminmanageplugins.php', ['subtype' => 'videotimeplugin'])
));

foreach (core_plugin_manager::instance()->get_plugins_of_type('videotimeplugin') as $plugin) {
    $plugin->load_settings($ADMIN, 'videotimepluginplugins', $hassiteconfig);
}
