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
 * Define all the backup steps that will be used by the backup_videotime_activity_task
 *
 * @package     mod_videotime
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete videotime structure for backup, with file and id annotations
 */
class backup_videotime_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        global $CFG;

        require_once($CFG->dirroot.'/mod/videotime/lib.php');

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $module = new backup_nested_element('videotime', ['id'], [
            'course',
            'name',
            'intro',
            'introformat',
            'vimeo_url',
            'video_description',
            'video_description_format',
            'timemodified',
            'completion_on_view_time',
            'completion_on_view_time_second',
            'completion_on_finish',
            'completion_on_percent',
            'completion_on_percent_value',
            'autoplay',
            'byline',
            'color',
            'height',
            'maxheight',
            'maxwidth',
            'muted',
            'playsinline',
            'portrait',
            'speed',
            'title',
            'transparent',
            'width',
            'responsive',
            'label_mode',
            'viewpercentgrade',
            'next_activity_button',
            'next_activity_id',
            'next_activity_auto',
            'resume_playback',
            'preview_picture',
            'show_description',
            'show_title',
            'show_tags',
            'show_duration',
            'show_viewed_duration',
            'columns'
        ]);

        if (videotime_has_pro()) {
            $sessions = new backup_nested_element('sessions');

            $session = new backup_nested_element('session', ['id'], [
                'id',
                'module_id',
                'user_id',
                'time',
                'timestarted',
                'state',
                'percent'
            ]);

            // Build the tree.
            $module->add_child($sessions);
            $sessions->add_child($session);
        }

        // Define sources.
        $module->set_source_table('videotime', array('id' => backup::VAR_ACTIVITYID));

        if (videotime_has_pro()) {
            if ($userinfo) {
                $session->set_source_table('videotime_session', ['module_id' => backup::VAR_MODID], 'id ASC');
            }

            // Define id annotations.
            $session->annotate_ids('user', 'user_id');
        }

        $module->annotate_ids('course_module', 'next_activity_id');

        // Define file annotations.
        $module->annotate_files('mod_videotime', 'intro', null); // This file area hasn't itemid.
        $module->annotate_files('mod_videotime', 'video_description', null); // This file area hasn't itemid.

        // Return the root element (videotime), wrapped into standard activity structure.
        return $this->prepare_activity_structure($module);

    }
}
