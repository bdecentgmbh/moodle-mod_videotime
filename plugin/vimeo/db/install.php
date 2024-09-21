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
 * Enable plugin for new install
 *
 * @package     videotimeplugin_vimeo
 * @copyright   2022 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotime\plugin_manager;

/**
 * Enable this plugin for new installs
 * @return bool
 */
function xmldb_videotimeplugin_vimeo_install() {
    global $DB;

    $dbman = $DB->get_manager();

    $manager = new plugin_manager('videotimeplugin');
    $manager->show_plugin('vimeo');

    $options = [
        'autoplay',
        'byline',
        'color',
        'controls',
        'height',
        'maxheight',
        'maxwidth',
        'muted',
        'option_loop',
        'playsinline',
        'portrait',
        'responsive',
        'speed',
        'title',
        'transparent',
        'width',
    ];

    $videojsoptions = [
        'autoplay',
        'controls',
        'height',
        'muted',
        'option_loop',
        'playsinline',
        'responsive',
        'speed',
        'width',
    ];

    $forced = [];
    $config = (array) get_config('videotime');

    foreach ($options as $option) {
        if (key_exists($option, $config)) {
            set_config($option, $config[$option], 'videotimeplugin_vimeo');
            if (in_array($option, $videojsoptions)) {
                set_config($option, $config[$option], 'videotimeplugin_videojs');
            }
            set_config($option, null, 'videotime');
            if (!empty($config[$option . '_force'])) {
                $forced[] = $option;
            }
            set_config($option . '_force', null, 'videotime');
        }
    }
    set_config('forced', implode(',', $forced), 'videotimeplugin_vimeo');
    set_config('forced', implode(',', array_intersect($forced, $videojsoptions)), 'videotimeplugin_videojs');

    // Copy embed settings to vimeo plugin table.
    $rs = $DB->get_recordset_select('videotime', []);
    foreach ($rs as $record) {
        $record->videotime = $record->id;
        unset($record->id);
        $DB->insert_record('videotimeplugin_vimeo', $record);
    }
    $rs->close();

    // Define field autoplay to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('autoplay');

    // Conditionally launch drop field autoplay.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field byline to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('byline');

    // Conditionally launch drop field byline.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field color to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('color');

    // Conditionally launch drop field color.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field controls to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('controls');

    // Conditionally launch drop field controls.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field height to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('height');

    // Conditionally launch drop field height.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field maxheight to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('maxheight');

    // Conditionally launch drop field maxheight.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field maxwidth to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('maxwidth');

    // Conditionally launch drop field maxwidth.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field muted to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('muted');

    // Conditionally launch drop field muted.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field playsinline to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('playsinline');

    // Conditionally launch drop field playsinline.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field portrait to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('portrait');

    // Conditionally launch drop field portrait.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field responsive to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('responsive');

    // Conditionally launch drop field responsive.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field speed to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('speed');

    // Conditionally launch drop field speed.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field title to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('title');

    // Conditionally launch drop field title.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field transparent to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('transparent');

    // Conditionally launch drop field transparent.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field width to be dropped from videotime.
    $table = new xmldb_table('videotime');
    $field = new xmldb_field('width');

    // Conditionally launch drop field width.
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Conditionally launch create table for videotime_session.
    if (!$dbman->table_exists('videotime_pro') && !$dbman->table_exists('videotimeplugin_pro')) {
        // Remove pro fields from main table.
        // Define field label_mode to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('label_mode');

        // Conditionally launch drop field resume_playback.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field resume_playback to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('resume_playback');

        // Conditionally launch drop field resume_playback.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field next_activity_button to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('next_activity_button');

        // Conditionally launch drop field next_activity_button.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field next_activity_id to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('next_activity_id');

        // Conditionally launch drop field next_activity_id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field next_activity_auto to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('next_activity_auto');

        // Conditionally launch drop field next_activity_auto.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field preventfastforwarding to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('preventfastforwarding');

        // Conditionally launch drop field preventfastforwarding.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field saveinterval to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('saveinterval');

        // Conditionally launch drop field saveinterval.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field dnt to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('dnt');

        // Conditionally launch drop field dnt.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field autopause to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('autopause');

        // Conditionally launch drop field autopause.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field background to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('background');

        // Conditionally launch drop field background.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field pip to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('pip');

        // Conditionally launch drop field pip.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    }

    if (!$dbman->table_exists('videotime_repository') && !$dbman->table_exists('videotimeplugin_repository')) {
        // Define field show_description to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_description');

        // Conditionally launch drop field show_description.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field show_title to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_title');

        // Conditionally launch drop field show_title.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field show_tags to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_tags');

        // Conditionally launch drop field show_tags.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field show_duration to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_duration');

        // Conditionally launch drop field show_duration.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field show_viewed_duration to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('show_viewed_duration');

        // Conditionally launch drop field show_viewed_duration.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field columns to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('columns');

        // Conditionally launch drop field columns.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field preview_picture to be dropped from videotime.
        $table = new xmldb_table('videotime');
        $field = new xmldb_field('preview_picture');

        // Conditionally launch drop field preview_picture.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    }

    return true;
}
